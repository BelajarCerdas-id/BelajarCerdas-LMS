@php
    $studentProfile = Auth::user()?->StudentProfile;
    $schoolPartner = $studentProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'sekolah';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $studentProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Siswa';
@endphp

    {{-- ============================================================ --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================ --}}
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72 min-h-screen" style="background: var(--color-surface); border-right: 1px solid var(--color-border); color: var(--color-text-primary);">

        {{-- LOGO SEKOLAH --}}
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

        {{-- DAFTAR NAVIGASI --}}
        <div class="flex-1 overflow-y-auto px-3 py-4">
            <ul class="space-y-1">
                <p class="px-3 pt-2 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Menu Utama
                </p>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.dashboard', [
                        'role'       => Auth::user()->role,
                        'schoolName' => $schoolName,
                        'schoolId'   => $schoolId
                    ]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-gauge"></i>
                        </span>
                        <span>Beranda</span>
                    </a>
                </li>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-school-flag"></i>
                        </span>
                        <span>LMS</span>
                    </a>
                </li>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.tka-tryout-period.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition font-medium text-[13.5px]">
                        <span class="w-7 h-7 rounded-lg bg-[#F1EFEA] text-[#5D5A52] flex items-center justify-center shrink-0 text-[13px]">
                            <i class="fa-solid fa-stopwatch"></i>
                        </span>
                        <span>Tryout TKA</span>
                    </a>
                </li>

                {{-- LIBRARY --}}
                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold text-[#A09D95] uppercase tracking-wider">
                    Sumber Belajar
                </p>

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

                        <a href="{{ route('teacher.library') }}"
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition cursor-pointer {{ $isLibraryActive ? 'bg-[#F1EFEA] text-dpai-accent' : 'text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg {{ $isLibraryActive ? 'bg-dpai-accent-soft text-dpai-accent' : 'bg-[#F1EFEA] text-[#5D5A52]' }} flex items-center justify-center shrink-0 text-[13px]">
                                    <i class="fa-solid fa-book"></i>
                                </span>
                                <span>Library</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200 {{ $isLibraryActive ? 'rotate-180' : '' }}"></i>
                        </a>

                        <div class="{{ $isLibraryActive ? 'flex flex-col pl-10 pr-2 py-1 space-y-0.5 border-l-2 border-[#EFEDE7] ml-6 my-1' : 'hidden pl-10 pr-2 py-1 space-y-0.5 border-l-2 border-[#EFEDE7] ml-6 my-1' }}">
                            <a href="{{ route('teacher.library.ppt') }}"
                               class="block py-1.5 px-2 text-[12.5px] rounded-lg transition {{ request()->routeIs('teacher.library.ppt') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]' }}">
                                Power Point
                            </a>

                            <a href="{{ route('teacher.library.lks') }}"
                               class="block py-1.5 px-2 text-[12.5px] rounded-lg transition {{ request()->routeIs('teacher.library.lks') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]' }}">
                                LKPD
                            </a>

                            <a href="{{ route('teacher.library.video') }}"
                               class="block py-1.5 px-2 text-[12.5px] rounded-lg transition {{ request()->routeIs('teacher.library.video') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]' }}">
                                Video
                            </a>

                            <a href="{{ route('student.tka-subject-list.view', ['role' => Auth::user()->role]) }}"
                               class="block py-1.5 px-2 text-[12.5px] rounded-lg transition {{ request()->routeIs('student.tka-subject-list.view') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]' }}">
                                Simulasi Soal TKA
                            </a>
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
                                    {{ Auth::user()->role ?? 'Siswa' }}
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8EFFE] text-[#3D7BFB] mt-1">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <a href="{{ route('lms.student.dashboard', [
                                    'role'       => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId'   => $schoolId
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
            <a href="{{ route('lms.student.dashboard', [
                'role'       => Auth::user()->role,
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
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
                    <a href="{{ route('lms.student.dashboard', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
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
                <div class="p-4 bg-[#F4F2EE] m-3 rounded-2xl flex items-center gap-3">
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
                            <a href="{{ route('lms.student.dashboard', [
                                'role'       => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId'   => $schoolId
                            ]) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-home text-[14px] w-5 text-center"></i>
                                <span class="text-[13.5px] font-medium">Beranda</span>
                            </a>
                        </li>

                        <li class="list-menu-sidebar-mobile-student">
                            <a href="{{ route('lms.student.view', [
                                'role'       => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId'   => $schoolId
                            ]) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-school-flag text-[14px] w-5 text-center"></i>
                                <span class="text-[13.5px] font-medium">LMS</span>
                            </a>
                        </li>

                        <li class="list-menu-sidebar-mobile-student">
                            <a href="{{ route('lms.student.tka-tryout-period.view', [
                                'role'       => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId'   => $schoolId
                            ]) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-stopwatch text-[14px] w-5 text-center"></i>
                                <span class="text-[13.5px] font-medium">Tryout TKA</span>
                            </a>
                        </li>

                        {{-- MOBILE LIBRARY --}}
                        <li class="list-menu-sidebar-mobile-student">
                            <div class="flex flex-col">
                                <a href="{{ route('student.library') }}"
                                   class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-book text-[14px] w-5 text-center"></i>
                                        <span class="text-[13.5px] font-medium">Library</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-xs text-[#8E8B84] {{ $isLibraryActive ? 'rotate-180' : '' }}"></i>
                                </a>

                                <div class="{{ $isLibraryActive ? 'flex flex-col pl-8 pr-2 py-1 space-y-0.5 border-l-2 border-[#EFEDE7] ml-4 my-1' : 'hidden pl-8 pr-2 py-1 space-y-0.5 border-l-2 border-[#EFEDE7] ml-4 my-1' }}">
                                    <a href="{{ route('teacher.library.ppt') }}"
                                       class="block py-1.5 px-2 text-[12.5px] rounded-lg {{ request()->routeIs('teacher.library.ppt') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:bg-[#F1EFEA]' }}">
                                        Power Point
                                    </a>

                                    <a href="{{ route('teacher.library.lks') }}"
                                       class="block py-1.5 px-2 text-[12.5px] rounded-lg {{ request()->routeIs('teacher.library.lks') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:bg-[#F1EFEA]' }}">
                                        LKPD
                                    </a>

                                    <a href="{{ route('teacher.library.video') }}"
                                       class="block py-1.5 px-2 text-[12.5px] rounded-lg {{ request()->routeIs('teacher.library.video') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:bg-[#F1EFEA]' }}">
                                        Video
                                    </a>

                                    <a href="{{ route('student.tka-subject-list.view', ['role' => Auth::user()->role]) }}"
                                       class="block py-1.5 px-2 text-[12.5px] rounded-lg {{ request()->routeIs('student.tka-subject-list.view') ? 'bg-[#F1EFEA] text-dpai-accent font-semibold' : 'text-[#5D5A52] hover:bg-[#F1EFEA]' }}">
                                        Simulasi Soal TKA
                                    </a>
                                </div>
                            </div>
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
