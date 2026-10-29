@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $schoolPartner = $staffProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'sekolah';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Guru';
@endphp
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72">

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
                <span class="text-center">
                    Logo sekolah tidak tersedia.
                </span>
            @endif
        </div>

        <div class="flex-1 bg-[#0071BC] text-white flex flex-col">

            <ul class="max-h-112.5 overflow-y-auto pb-6 pt-6 space-y-3">

                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                        <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                        <a href="{{ route('lms.teacher.view', [
                                'role' => Auth::user()->role, 
                                'schoolName' => $schoolName, 
                                'schoolId' => $schoolId
                            ]) }}" class="link-href text-[14px]">
                            Beranda
                        </a>
                    </div>
                </li>

                {{-- ====================================================== --}}
                {{-- REGISTRASI GURU --}}
                {{-- ====================================================== --}}
                <li class="list-item">

                    <div class="dropdown-menu w-full flex flex-col items-start">

                        {{-- TOGGLE REGISTRASI GURU --}}
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">

                            {{-- ICON --}}
                            <i class="fa-solid fa-user-plus text-[15px] w-5 text-center"></i>

                            {{-- NAMA MENU --}}
                            <span class="text-[14px]">
                                Administrasi Guru
                            </span>

                            {{-- CHEVRON --}}
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>

                        </div>


                        {{-- ================================================== --}}
                        {{-- DROPDOWN CONTENT --}}
                        {{-- ================================================== --}}
                        <div class="content-dropdown pl-6 pr-3.5 w-full">


                            {{-- ================================================== --}}
                            {{-- 1. ANALISIS CP HINGGA ATP --}}
                            {{-- ================================================== --}}
                            <div class="flex flex-col py-2 mt-1">

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                    Analisis CP hingga ATP

                                </a>

                            </div>


                            {{-- ================================================== --}}
                            {{-- 2. PROTA DAN PROSEM --}}
                            {{-- ================================================== --}}
                            <div class="flex flex-col py-2 mt-1">

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                    PROTA & PROSEM

                                </a>

                            </div>


                            {{-- ================================================== --}}
                            {{-- 3. ANALISIS RPPM DAN RPPM --}}
                            {{-- ================================================== --}}
                            <div class="flex flex-col py-2 mt-1">

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                    RPP & RPPM

                                </a>

                            </div>


                            {{-- ================================================== --}}
                            {{-- 4. REFLEKSI GURU --}}
                            {{-- ================================================== --}}
                            <div class="flex flex-col py-2 mt-1">

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                    Refleksi Guru

                                </a>

                            </div>


                        </div>

                    </div>

                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-person-chalkboard text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Aktivitas Guru</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Assessment</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherAssessmentManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Mangement
                                    </a>
                                    <a href="{{ route('lms.assessmentGradingManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Belum Dinilai
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Content</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherContentManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Content Management
                                    </a>
                                    <a href="{{ route('lms.teacherContentForRelease.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Content For Release
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Question</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherQuestionBankManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Question Bank Management
                                    </a>
                                    <a href="{{ route('lms.teacherQuestionBankForRelease.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Question Bank For Release
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- MEJA GURU -->
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-folder-open text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Meja Guru</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col">
                                <a href="{{ route('lms.teacherClassListSubjectAttendance.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Presensi Mata Pelajaran
                                </a>

                                <a href="{{ route('lms.teacherDailyAgenda.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Agenda Harian
                                </a>

                                <a href="{{ route('lms.teacherClassList.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Buku Nilai
                                </a>

                                <a href="{{ route('lms.teacherClassListGradeLedger.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Leger Nilai
                                </a>

                                <a href="{{ route('lms.teacherClassListAcademicTranscript.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Transkrip Nilai
                                </a>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- INFORMASI -->
                <li class="list-item mt-1">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-circle-info text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Informasi</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col py-2 mt-1">
                                <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                    Polling
                                </a>
                            </div>
                        </div>

                    </div>
                </li>
            </ul>

            <div class="mt-auto">
                <hr class="border-white border opacity-60 mb-10 mx-6">

                <div class="pb-16 -ml-2">
                    <span class="text-sm mb-6 flex justify-center font-bold">
                        Partnership By:
                    </span>

                    <div class="flex justify-center w-full">
                        <img
                            src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-12 object-contain"
                        >
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

                            <div
                                class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-gray-200 shadow-lg w-55 rounded-lg">
                                <a href="{{ route('lms.teacher.view', [
                                    'role' => Auth::user()->role, 
                                    'schoolName' => $schoolName, 
                                    'schoolId' => $schoolId
                                ]) }}">
                                    <div class="flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-house"></i>
                                        Beranda
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
                                    <button
                                        class="w-full flex items-center pl-3.5 py-3 gap-2 text-[13px] hover:bg-gray-100 cursor-pointer">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-white"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('lms.teacher.view', [
                                'role' => Auth::user()->role, 
                                'schoolName' => $schoolName, 
                                'schoolId' => $schoolId
                            ]) }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
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
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-base ml-0.75"></i>
                                    Keluar
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
            <a href="{{ route('lms.teacher.view', [
                'role' => Auth::user()->role, 
                'schoolName' => $schoolName, 
                'schoolId' => $schoolId
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
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('lms.teacher.view', [
                                'role' => Auth::user()->role, 
                                'schoolName' => $schoolName, 
                                'schoolId' => $schoolId
                            ]) }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
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
                            <button
                                class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base ml-0.75"></i>
                                Keluar
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

            <div class="bg-gray-50 h-full min-w-[65vw] flex flex-col">

                <header class="h-20 px-4 bg-[#0071BC] flex items-center justify-between shadow-sm">
                    <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" class="h-9 object-contain" alt="Belajar Cerdas">

                    <label for="my-drawer-1">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                    </label>
                </header>

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

                <ul class="max-h-112.5 overflow-y-auto pb-6 pt-6 space-y-3">
                    
                    <li class="list-item">
                        <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                            <a href="{{ route('lms.teacher.view', [
                                    'role' => Auth::user()->role, 
                                    'schoolName' => $schoolName, 
                                    'schoolId' => $schoolId
                                ]) }}" class="link-href text-[14px]">
                                Beranda
                            </a>
                        </div>
                    </li>

                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-user-plus text-[15px] w-5 text-center"></i>

                                <span class="text-[14px]">
                                    Informasi Guru
                                </span>

                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                        Analisis CP hingga ATP
                                    </a>
                                </div>

                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                        PROTA & PROSEM
                                    </a>
                                </div>

                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                        RPP & RPPM
                                    </a>
                                </div>

                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">

                                        Refleksi Guru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-person-chalkboard text-[15px] w-5 text-center"></i>
                                <span class="text-[14px]">Aktivitas Guru</span>
                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Assessment</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherAssessmentManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => $schoolName,
                                                'schoolId' => $schoolId
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Assessment Mangement
                                        </a>
                                        <a href="{{ route('lms.assessmentGradingManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Belum Dinilai
                                    </a>
                                </div>
                                </div>
                                
                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Content</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherContentManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => $schoolName ?? 'sekolah',
                                                'schoolId' => $schoolId ?? 1
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Content Mangement
                                        </a>
                                        <a href="{{ route('lms.teacherContentForRelease.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => $schoolName ?? 'sekolah',
                                                'schoolId' => $schoolId ?? 1
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Content For Release
                                        </a>
                                    </div>
                                </div>

                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Question</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherQuestionBankManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => $schoolName ?? 'sekolah',
                                                'schoolId' => $schoolId ?? 1
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Question Bank Management
                                        </a>
                                        <a href="{{ route('lms.teacherQuestionBankForRelease.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => $schoolName ?? 'sekolah',
                                                'schoolId' => $schoolId ?? 1
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Question Bank For Release
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- MEJA GURU -->
                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-folder-open text-[15px] w-5 text-center"></i>
                                <span class="text-[14px]">Meja Guru</span>
                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col">
                                    <a href="{{ route('lms.teacherClassListSubjectAttendance.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Presensi Mata Pelajaran
                                    </a>

                                    <a href="{{ route('lms.teacherDailyAgenda.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Agenda Harian
                                    </a>

                                    <a href="{{ route('lms.teacherClassList.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Buku Nilai
                                    </a>

                                    <a href="{{ route('lms.teacherClassListGradeLedger.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Leger Nilai
                                    </a>
                                    <a href="{{ route('lms.teacherClassListAcademicTranscript.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Transkrip Nilai
                                    </a>
                                </div>
                            </div>

                        </div>
                    </li>

                    <!-- INFORMASI -->
                    <li class="list-item mt-1">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-circle-info text-[15px] w-5 text-center"></i>
                                <span class="text-[14px]">Informasi</span>
                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? 1]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                        Polling
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </li>
                </ul>

                <div class="p-4 border-t border-gray-300">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            class="w-full flex items-center justify-center py-3 bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95">
                            <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
