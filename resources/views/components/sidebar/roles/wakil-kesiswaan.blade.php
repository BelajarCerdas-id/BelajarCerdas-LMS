@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $schoolPartner = $staffProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'sekolah';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Wakil Kesiswaan';
@endphp

    {{-- ============================================================ --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================ --}}
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72 min-h-screen" style="background: var(--color-surface); border-right: 1px solid var(--color-border); color: var(--color-text-primary);">

        {{-- LOGO & IDENTITAS SEKOLAH --}}
        <div class="h-36 bg-white flex flex-col items-center justify-center border-b border-[#EFEDE7] px-4 py-3 relative group">
            <div id="logo-wrapper" class="flex items-center justify-center overflow-hidden">
                @if(!empty($schoolLogo))
                    <img id="school-logo-preview" src="{{ asset($schoolLogo) }}" alt="{{ $schoolName }}" class="max-h-20 max-w-[85%] object-contain transition-transform duration-200 group-hover:scale-105">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-dpai-accent-soft text-dpai-accent flex items-center justify-center text-2xl shadow-sm">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                @endif
            </div>

            @if(empty($schoolLogo))
                <span class="text-xs text-[#8E8B84] text-center font-medium mt-2 line-clamp-1">
                    {{ $schoolName }}
                </span>
            @endif
        </div>

        {{-- DAFTAR NAVIGASI SIDEBAR --}}
        <div class="flex-1 overflow-y-auto px-3 py-4">
            <ul class="space-y-1">

                {{-- KATEGORI: UTAMA --}}
                <p class="px-3 pt-2 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Utama
                </p>

                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <a href="{{ route('lms.student-vice-principal.dashboard.view', [
                            'role' => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId
                        ]) }}" class="link-href flex-1 text-[13.5px]">
                            Beranda
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: KESISWAAN --}}
                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Kesiswaan
                </p>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                            <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                <i class="fa-solid fa-chart-line"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Monitoring Siswa</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                <a href="{{ route('lms.student-vice-principal.reflection-management.view', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId
                                ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Refleksi Harian
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                            <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                <i class="fa-solid fa-shapes"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Ekstrakurikuler</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                <a href="{{ route('lms.student-vice-principal.extracurricular-management.view', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId
                                ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    List Ekstrakurikuler
                                </a>

                                <a href="{{ route('lms.student-vice-principal.extracurricular-management.kelengkapan', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId
                                ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Kelengkapan
                                </a>
                            </div>
                        </div>
                    </div>
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
                                    {{ Auth::user()->role ?? 'Wakil Kesiswaan' }}
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

                            <a href="{{ route('lms.student-vice-principal.dashboard.view', [
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
            <a href="{{ route('lms.student-vice-principal.dashboard.view', [
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
                    <a href="{{ route('lms.student-vice-principal.dashboard.view', [
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

                {{-- USER PROFILE CARD --}}
                <div class="p-4 border-b border-[#EFEDE7] bg-[#F4F2EE]">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-dpai-accent text-white flex items-center justify-center font-bold text-base shadow-sm">
                            {{ strtoupper(substr($profileName, 0, 1)) }}
                        </span>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-[#1B1913] line-clamp-1">{{ $profileName }}</span>
                            <span class="text-xs text-[#8E8B84]">{{ Auth::user()->role }}</span>
                        </div>
                    </div>
                </div>

                {{-- DRAWER NAVIGATION --}}
                <ul class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                    <p class="px-3 pt-2 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                        Utama
                    </p>

                    <li class="list-item">
                        <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                            <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                <i class="fa-solid fa-house"></i>
                            </span>
                            <a href="{{ route('lms.student-vice-principal.dashboard.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                                Beranda
                            </a>
                        </div>
                    </li>

                    <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                        Kesiswaan
                    </p>

                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                                <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                    <i class="fa-solid fa-chart-line"></i>
                                </span>
                                <span class="flex-1 text-[13.5px]">Monitoring Siswa</span>
                                <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                            </div>

                            <div class="content-dropdown pl-10 pr-2 w-full">
                                <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                    <a href="{{ route('lms.student-vice-principal.reflection-management.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                        Refleksi Harian
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                                <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                                    <i class="fa-solid fa-shapes"></i>
                                </span>
                                <span class="flex-1 text-[13.5px]">Ekstrakurikuler</span>
                                <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                            </div>

                            <div class="content-dropdown pl-10 pr-2 w-full">
                                <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                    <a href="{{ route('lms.student-vice-principal.extracurricular-management.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                        List Ekstrakurikuler
                                    </a>

                                    <a href="{{ route('lms.student-vice-principal.extracurricular-management.kelengkapan', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                        Kelengkapan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>

                {{-- LOGOUT BUTTON --}}
                <div class="p-4 border-t border-[#EFEDE7]">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 bg-[#FBEAE7] text-[#DE3B2B] font-semibold text-sm rounded-xl gap-2 cursor-pointer transition hover:bg-[#F8D5CE] active:scale-98">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
