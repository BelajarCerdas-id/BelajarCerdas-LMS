@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $schoolPartner = $staffProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'sekolah';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Admin Sekolah';
@endphp

    {{-- ============================================================ --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================ --}}
    <aside class="sidebar-beranda-school-admin hidden md:flex flex-col w-72 min-h-screen" style="background: var(--color-surface); border-right: 1px solid var(--color-border); color: var(--color-text-primary);">

        {{-- LOGO & IDENTITAS SEKOLAH DENGAN FITUR UBAH LOGO --}}
        <div class="h-36 bg-white flex flex-col items-center justify-center border-b border-[#EFEDE7] px-4 py-3 relative group">
            <div id="logo-wrapper" class="flex items-center justify-center overflow-hidden">
                @if(!empty($schoolLogo))
                    <img id="school-logo-preview" src="{{ asset($schoolLogo) }}" alt="{{ $schoolName }}" class="max-h-20 max-w-[85%] object-contain transition-transform duration-200 group-hover:scale-105">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-dpai-accent-soft text-dpai-accent flex items-center justify-center text-2xl shadow-sm">
                        <i class="fa-solid fa-school"></i>
                    </div>
                @endif
            </div>

            @if(empty($schoolLogo))
                <span id="logo-text" class="text-xs text-[#8E8B84] text-center font-medium mt-2 line-clamp-1">
                    {{ $schoolName }}
                </span>
            @endif

            @if(Auth::user()->role === 'Admin Sekolah')
                {{-- OVERLAY UBAH LOGO --}}
                <label id="btn-open-edit-logo-dekstop"
                    class="absolute inset-0 bg-[#1D1B16]/60 backdrop-blur-[2px] flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer text-white">
                    <i class="fa-solid fa-pen text-lg mb-1"></i>
                    <span class="text-xs font-semibold">Ubah Logo</span>
                </label>

                <input type="file" id="upload-logo" class="hidden" accept="image/*">
            @endif
        </div>

        {{-- DAFTAR NAVIGASI SIDEBAR --}}
        <div class="flex-1 overflow-y-auto px-3 py-4">
            <ul class="space-y-1">
                <p class="px-3 pt-2 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Menu Utama
                </p>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.schoolAdmin.dashboard.view', [
                            'role' => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId
                        ]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span>Beranda</span>
                    </a>
                </li>

                {{-- INFORMASI GURU --}}
                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Administrasi
                </p>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                            <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                <i class="fa-solid fa-user-plus"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Informasi Guru</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Analisis CP hingga ATP
                                </a>

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    PROTA & PROSEM
                                </a>

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    RPP & RPPM
                                </a>

                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Refleksi Guru
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- LMS --}}
                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Pembelajaran
                </p>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.academicManagement.view', [
                            'role' => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId
                        ]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-school-flag"></i>
                        </span>
                        <span>LMS</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- FOOTER PARTNERSHIP (STICKY DI BAWAH) --}}
        <div class="p-3 border-t border-[#EFEDE7] shrink-0 bg-white sticky bottom-0 z-10">
            <div class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl bg-[#F4F2EE]">
                <span class="text-[10px] font-bold text-[#8E8B84] tracking-wide uppercase">
                    Partnership By:
                </span>
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-5 object-contain">
            </div>
        </div>
    </aside>

    {{-- ============================================================ --}}
    {{-- DESKTOP TOP HEADER / NAVBAR --}}
    {{-- ============================================================ --}}
    <div class="relative left-72.5 w-[calc(100%-290px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <div class="w-full h-18 bg-white border-b border-[#EFEDE7] shadow-[0_1px_3px_rgba(27,25,19,0.03)] flex items-center justify-between px-6 sm:px-8">
                
                {{-- TITLE & BACK BUTTON --}}
                <header class="text-[20px] font-bold text-[#1B1913] flex items-center gap-3 tracking-tight">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}" class="inline-flex items-center gap-2 text-[#5D5A52] hover:text-dpai-accent transition">
                            @if (isset($backButton))
                                <button type="button" class="cursor-pointer">{!! $backButton !!}</button>
                            @else
                                <i class="fa-solid fa-arrow-left text-lg"></i>
                            @endif
                            <span class="text-xl font-bold text-[#1B1913]">{{ $headerSideNav ?? '' }}</span>
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button type="button" class="cursor-pointer text-[#5D5A52] hover:text-dpai-accent">{!! $backButton !!}</button>
                                <span class="font-bold text-xl text-[#1B1913]">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl text-[#1B1913]">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                {{-- PROFILE ACTION AREA --}}
                <div class="list-item-button-profile relative z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile flex items-center gap-3 py-1.5 px-3 rounded-full border border-[#EFEDE7] bg-[#F4F2EE] hover:bg-[#F1EFEA] cursor-pointer transition shadow-xs">
                            <div class="flex flex-col text-right leading-tight">
                                <span class="text-[13px] font-bold text-[#1B1913]">
                                    {{ Str::limit($profileName ?? '', 22) }}
                                </span>
                                <span class="text-[11px] font-semibold text-[#8E8B84]">
                                    {{ Auth::user()->role ?? 'Admin Sekolah' }}
                                </span>
                            </div>
                            
                            {{-- AVATAR TILE --}}
                            <span class="w-9 h-9 rounded-full bg-dpai-accent text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                {{ strtoupper(substr($profileName, 0, 1)) }}
                            </span>
                        </div>

                        {{-- DROPDOWN MENU PROFIL --}}
                        <div class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-[#EFEDE7] shadow-xl w-60 rounded-2xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#EFEDE7]">
                                <p class="text-xs text-[#8E8B84]">Masuk sebagai</p>
                                <p class="text-sm font-bold text-[#1B1913] truncate">{{ $profileName }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-dpai-accent-soft text-[#B23A16] mt-1">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <a href="{{ route('lms.schoolAdmin.dashboard.view', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId
                                ]) }}" class="flex items-center px-4 py-2.5 gap-2.5 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA] hover:text-[#1B1913] transition font-medium">
                                <i class="fa-solid fa-house text-[#8E8B84]"></i>
                                Beranda
                            </a>

                            <a href="{{ route('profile-account-school-partner.view', [
                                    'role'       => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId'   => $schoolId
                                ]) }}" class="flex items-center px-4 py-2.5 gap-2.5 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA] hover:text-[#1B1913] transition font-medium">
                                <i class="fa-regular fa-circle-user text-[#8E8B84]"></i>
                                Profil Akun
                            </a>

                            <div class="border-t border-[#EFEDE7] my-1"></div>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2.5 gap-2.5 text-[13px] text-[#DE3B2B] hover:bg-[#FBEAE7] transition font-medium cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MOBILE NAVBAR & DRAWER --}}
    {{-- ============================================================ --}}
    <nav class="navbar-beranda-phone w-full h-16 flex justify-between items-center md:hidden bg-white border-b border-[#EFEDE7] px-4 shadow-xs">
        <div class="flex items-center gap-3">
            <label for="my-drawer-1" class="w-9 h-9 rounded-xl bg-[#F4F2EE] flex items-center justify-center text-[#1B1913] cursor-pointer">
                <i class="fas fa-bars text-lg"></i>
            </label>
            <a href="{{ route('lms.schoolAdmin.dashboard.view', [
                    'role' => Auth::user()->role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId
                ]) }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-6">
            </a>
        </div>

        <div class="list-item-button-profile relative md:hidden z-40">
            <div class="dropdown-menu">
                <div class="toggle-menu-button-profile w-9 h-9 rounded-full bg-dpai-accent text-white flex items-center justify-center font-bold text-sm cursor-pointer shadow-sm">
                    {{ strtoupper(substr($profileName, 0, 1)) }}
                </div>
                <div class="content-dropdown-button-profile absolute bg-white border border-[#EFEDE7] shadow-xl w-48 rounded-2xl py-2 mt-2 right-0">
                    <a href="{{ route('lms.schoolAdmin.dashboard.view', [
                            'role' => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId
                        ]) }}" class="flex items-center px-3 py-2 gap-2 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA]">
                        <i class="fa-solid fa-house"></i>
                        Beranda
                    </a>
                    
                    <a href="{{ route('profile-account-school-partner.view', [
                        'role'       => Auth::user()->role,
                        'schoolName' => $schoolName,
                        'schoolId'   => $schoolId
                    ]) }}" class="flex items-center px-3 py-2 gap-2 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA]">
                        <i class="fa-regular fa-circle-user"></i>
                        Profil Akun
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-2 gap-2 text-[13px] text-[#DE3B2B] hover:bg-[#FBEAE7] cursor-pointer">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- MOBILE DRAWER --}}
    <div class="drawer md:hidden z-[9999]">
        <input id="my-drawer-1" type="checkbox" class="drawer-toggle"/>

        <div class="drawer-side">
            <label for="my-drawer-1" class="drawer-overlay"></label>

            <div class="bg-white h-full min-w-[75vw] max-w-[85vw] flex flex-col border-r border-[#EFEDE7]">

                {{-- DRAWER HEADER --}}
                <header class="h-16 px-4 bg-white border-b border-[#EFEDE7] flex items-center justify-between">
                    <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" class="h-6 object-contain" alt="Belajar Cerdas">

                    <label for="my-drawer-1" class="w-8 h-8 rounded-lg bg-[#F4F2EE] flex items-center justify-center text-[#5D5A52] cursor-pointer">
                        <i class="fas fa-xmark text-lg"></i>
                    </label>
                </header>

                {{-- CARD SEKOLAH DENGAN OVERLAY UBAH LOGO --}}
                <div id="btn-open-edit-logo-mobile" class="relative flex flex-col items-center cursor-pointer p-4 bg-[#F4F2EE] m-3 rounded-2xl group">
                    @if(!empty($schoolLogo))
                        <img src="{{ asset($schoolLogo) }}" class="h-16 object-contain" alt="Logo Sekolah">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center border border-[#EFEDE7] mb-1">
                            <i class="fa-solid fa-school text-xl text-dpai-accent"></i>
                        </div>
                        <span class="text-xs text-[#5D5A52] font-semibold text-center">{{ $schoolName }}</span>
                        <span class="text-[10px] text-[#8E8B84]">Ketuk untuk ubah logo</span>
                    @endif

                    <div class="absolute inset-0 bg-[#1D1B16]/60 rounded-2xl flex flex-col items-center justify-center opacity-0 hover:opacity-100 transition text-white">
                        <i class="fa-solid fa-pen text-base mb-1"></i>
                        <span class="text-[11px] font-semibold">Ubah Logo</span>
                    </div>
                </div>

                {{-- USER PROFILE CARD --}}
                <div class="px-4 py-2 border-b border-[#EFEDE7] flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-dpai-accent text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                        {{ strtoupper(substr($profileName, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-[#1B1913] truncate leading-tight">
                            {{ $profileName }}
                        </p>
                        <p class="text-[11px] font-semibold text-[#8E8B84]">
                            {{ Auth::user()->role }}
                        </p>
                    </div>
                </div>

                {{-- DRAWER NAVIGATION --}}
                <div class="flex-1 overflow-y-auto px-3 py-2">
                    <ul class="space-y-1">
                        <li class="list-menu-sidebar-mobile-student">
                            <a href="{{ route('lms.schoolAdmin.dashboard.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-home text-[14px] w-5 text-center"></i>
                                <span class="text-[13.5px] font-medium">Beranda</span>
                            </a>
                        </li>

                        {{-- Administrasi Guru --}}
                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-user-plus text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">Informasi Guru</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">
                                            Analisis CP hingga ATP
                                        </a>
                                        <a href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">
                                            PROTA & PROSEM
                                        </a>
                                        <a href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">
                                            RPP & RPPM
                                        </a>
                                        <a href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">
                                            Refleksi Guru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- LMS --}}
                        <li class="list-menu-sidebar-mobile-student">
                            <a href="{{ route('lms.academicManagement.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-school-flag text-[14px] w-5 text-center"></i>
                                <span class="text-[13.5px] font-medium">LMS</span>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- DRAWER LOGOUT BUTTON --}}
                <div class="p-4 border-t border-[#EFEDE7]">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 px-4 bg-[#FBEAE7] text-[#DE3B2B] rounded-xl font-bold text-sm gap-2 cursor-pointer transition active:scale-95">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
        
    <div id="alert-success-insert-school-logo"></div>

    {{-- MODAL UBAH LOGO SEKOLAH --}}
    <dialog id="edit-school-logo" class="modal">
        <div class="modal-box bg-white w-105 rounded-2xl border border-[#EFEDE7] shadow-2xl p-6">
            <form id="edit-school-logo-form" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                <h3 class="font-bold text-lg text-center text-[#1B1913] mb-6">
                    Ubah Logo Sekolah
                </h3>
    
                {{-- PREVIEW --}}
                <div class="flex justify-center mb-6">
                    <div id="preview-container" class="w-32 h-32 flex items-center justify-center transition-all duration-200">
                        <div id="preview-inner" class="w-full h-full flex items-center justify-center {{ $schoolLogo ? '' : 'rounded-2xl bg-[#F4F2EE] border border-[#EFEDE7] overflow-hidden' }}">
                            <img id="preview-logo-modal" src="{{ !empty($schoolLogo) ? asset($schoolLogo) : '' }}" 
                                 class="max-w-full max-h-full object-contain {{ empty($schoolLogo) ? 'hidden' : '' }}">
                            @if(empty($schoolLogo))
                                <i id="icon-logo-modal" class="fa-solid fa-school text-3xl text-[#8E8B84]"></i>
                            @endif
                        </div>
                    </div>
                </div>
    
                {{-- UPLOAD AREA --}}
                <label for="input-school-logo"
                    class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-[#EFEDE7] rounded-xl cursor-pointer hover:border-dpai-accent hover:bg-dpai-accent-soft/20 transition">
                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-[#8E8B84] mb-2"></i>
                    <span class="text-sm font-medium text-[#5D5A52]">
                        Klik untuk upload logo
                    </span>
                    <span class="text-xs text-[#8E8B84]">
                        JPG, JPEG, PNG (Maks. 2MB)
                    </span>
                </label>
    
                <input id="input-school-logo" name="school_logo" type="file" class="hidden" onchange="previewSchoolLogoInput(event, 'input-school-logo')" accept=".jpg, .jpeg, .png">
    
                <p id="error-school_logo" class="text-[#DE3B2B] text-xs mt-2"></p>
    
                <div class="flex justify-end gap-3 mt-6">
                    <button id="btn-save-school-logo" class="bg-dpai-accent text-white px-6 py-2 rounded-xl font-bold cursor-pointer outline-none hover:bg-[#E0481F] shadow-[0_4px_12px_rgba(240,85,42,0.25)] transition disabled:cursor-default">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
