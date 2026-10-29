@php
    $officeProfile = Auth::user()?->OfficeProfile;
    $profileName = $officeProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Administrator';
@endphp

    {{-- ============================================================ --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================ --}}
    <aside class="sidebar-beranda-administrator hidden md:flex flex-col w-72 min-h-screen" style="background: var(--color-surface); border-right: 1px solid var(--color-border); color: var(--color-text-primary);">

        {{-- LOGO BELAJAR CERDAS --}}
        <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="block shrink-0">
            <div class="h-20 flex items-center justify-center px-4" style="background: var(--color-surface); border-bottom: 1px solid var(--color-border);">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-8 max-w-[190px] object-contain">
            </div>
        </a>

        {{-- DAFTAR NAVIGASI SIDEBAR --}}
        <div class="flex-1 overflow-y-auto px-3 py-4 custom-sidebar-scroll">
            <ul class="space-y-1">
                
                {{-- KATEGORI: UTAMA --}}
                <p class="px-3 pt-2 pb-1.5 text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">
                    Utama
                </p>

                <li class="list-item {{ request()->routeIs('lms.office.dashboard.view') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 transition font-medium text-[13.5px] {{ request()->routeIs('lms.office.dashboard.view') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('lms.office.dashboard.view') ? 'bg-white text-dpai-accent shadow-xs' : 'text-[#5D5A52]' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('lms.office.dashboard.view') ? 'background: var(--color-surface-muted);' : '' }}">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('lms.office.dashboard.view') ? 'active' : '' }}">
                            Beranda
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: AKADEMIK & KURIKULUM --}}
                <p class="px-3 pt-4 pb-1.5 text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">
                    Akademik & Ujian
                </p>

                <li class="list-item {{ request()->routeIs('kurikulum.*') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 transition font-medium text-[13.5px] {{ request()->routeIs('kurikulum.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('kurikulum.*') ? 'bg-white text-dpai-accent shadow-xs' : 'text-[#5D5A52]' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('kurikulum.*') ? 'background: var(--color-surface-muted);' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                        <a href="{{ route('kurikulum.view') }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('kurikulum.*') ? 'active' : '' }}">
                            Manajemen Kurikulum
                        </a>
                    </div>
                </li>

                <li class="list-item {{ request()->routeIs('lms.office.tka-tryout-period-management.*') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 transition font-medium text-[13.5px] {{ request()->routeIs('lms.office.tka-tryout-period-management.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('lms.office.tka-tryout-period-management.*') ? 'bg-white text-dpai-accent shadow-xs' : 'text-[#5D5A52]' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('lms.office.tka-tryout-period-management.*') ? 'background: var(--color-surface-muted);' : '' }}">
                            <i class="fa-solid fa-stopwatch"></i>
                        </span>
                        <a href="{{ route('lms.office.tka-tryout-period-management.view', ['role' => Auth::user()->role]) }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('lms.office.tka-tryout-period-management.*') ? 'active' : '' }}">
                            Tryout TKA
                        </a>
                    </div>
                </li>

                <li class="list-item {{ request()->routeIs('lms.office.analytics.*') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 transition font-medium text-[13.5px] {{ request()->routeIs('lms.office.analytics.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('lms.office.analytics.*') ? 'bg-white text-dpai-accent shadow-xs' : 'text-[#5D5A52]' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('lms.office.analytics.*') ? 'background: var(--color-surface-muted);' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                        <a href="{{ route('lms.office.analytics.view', Auth::user()->role) }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('lms.office.analytics.*') ? 'active' : '' }}">
                            Site Analytics
                        </a>
                    </div>
                </li>

                <li class="list-item {{ request()->routeIs('lms.office.assessmentDebug.*') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2.5 transition font-medium text-[13.5px] {{ request()->routeIs('lms.office.assessmentDebug.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('lms.office.assessmentDebug.*') ? 'bg-white text-dpai-accent shadow-xs' : 'text-[#5D5A52]' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('lms.office.assessmentDebug.*') ? 'background: var(--color-surface-muted);' : '' }}">
                            <i class="fa-solid fa-wrench"></i>
                        </span>
                        <a href="{{ route('lms.office.assessmentDebug.view', Auth::user()->role) }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('lms.office.assessmentDebug.*') ? 'active' : '' }}">
                            Exam Debugger
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: LIBRARY --}}
                <p class="px-3 pt-4 pb-1.5 text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">
                    Pustaka
                </p>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA] transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] text-[#5D5A52]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted);">
                                <i class="fa-solid fa-book"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Library</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('library.administrator') }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Library
                                </a>
                                <a href="{{ route('topik.management') }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Topik Management
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- KATEGORI: MANAJEMEN SISTEM --}}
                <p class="px-3 pt-4 pb-1.5 text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">
                    Manajemen Sistem
                </p>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA] transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] text-[#5D5A52]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted);">
                                <i class="fa-solid fa-building-user"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Office Management</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.officeManagement.manage-user.view', ['role' => Auth::user()->role]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Manage User
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA] transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] text-[#5D5A52]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted);">
                                <i class="fa-solid fa-book-bookmark"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Belajar Cerdas LMS</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1">
                                <div class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-1.5 px-2 rounded-lg text-[13px] font-medium text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]">
                                    <span>Content</span>
                                    <i class="fas fa-chevron-down text-[10px] text-[#8E8B84]" id="rotate-icon-2"></i>
                                </div>
                                <div class="list-content-dropdown pl-3 border-l-2 my-1 space-y-0.5" style="border-color: var(--color-border);">
                                    <a href="{{ route('lms.contentManagement.view.noSchoolPartner', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                        Manage Content
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col py-1">
                                <div class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-1.5 px-2 rounded-lg text-[13px] font-medium text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA]">
                                    <span>Question</span>
                                    <i class="fas fa-chevron-down text-[10px] text-[#8E8B84]" id="rotate-icon-2"></i>
                                </div>
                                <div class="list-content-dropdown pl-3 border-l-2 my-1 space-y-0.5" style="border-color: var(--color-border);">
                                    <a href="{{ route('lms.questionBankManagement.view.noSchoolPartner', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 px-2 rounded-lg text-[12px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                        Manage Question
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA] transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] text-[#5D5A52]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted);">
                                <i class="fa-solid fa-school-flag"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">School Partner</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.schoolSubscription.view', ['role' => Auth::user()->role]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    LMS
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA] transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] text-[#5D5A52]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted);">
                                <i class="fa-solid fa-building-columns"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Yayasan</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#8E8B84] transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.schoolFoundation.manage.view', ['role' => Auth::user()->role]) }}"
                                   class="link-href block py-1.5 px-2 rounded-lg text-[12.5px] text-[#5D5A52] hover:text-[#1B1913] hover:bg-[#F1EFEA] transition">
                                    Manajemen Yayasan
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        {{-- FOOTER INFO (STICKY DI BAWAH) --}}
        <div class="p-3 border-t shrink-0 sticky bottom-0 z-10" style="border-color: var(--color-border); background: var(--color-surface);">
            <div class="flex flex-col items-center justify-center gap-1.5 p-2.5 text-center" style="background: var(--color-surface-muted); border-radius: var(--radius-card); border: 1px solid var(--color-border);">
                <span class="text-[11px] font-bold tracking-wider uppercase" style="color: var(--color-text-secondary);">
                    Admin Console
                </span>
                <span class="text-[10px]" style="color: var(--color-text-muted);">Belajar Cerdas v2.0</span>
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
                                    {{ Auth::user()->role ?? 'Administrator' }}
                                </span>
                            </div>
                            
                            {{-- AVATAR TILE --}}
                            <span class="w-9 h-9 rounded-full bg-[#1D1B16] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                {{ strtoupper(substr($profileName, 0, 1)) }}
                            </span>
                        </div>

                        {{-- DROPDOWN MENU PROFIL --}}
                        <div class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-[#EFEDE7] shadow-xl w-60 rounded-2xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#EFEDE7]">
                                <p class="text-xs text-[#8E8B84]">Masuk sebagai</p>
                                <p class="text-sm font-bold text-[#1B1913] truncate">{{ $profileName }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#1D1B16] text-white mt-1">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="flex items-center px-4 py-2.5 gap-2.5 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA] hover:text-[#1B1913] transition font-medium">
                                <i class="fa-solid fa-house text-[#8E8B84]"></i>
                                Beranda
                            </a>

                            <a href="{{ route('profile-account-non-school-partner.view', ['role' => Auth::user()->role]) }}" class="flex items-center px-4 py-2.5 gap-2.5 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA] hover:text-[#1B1913] transition font-medium">
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
            <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-6">
            </a>
        </div>

        <div class="list-item-button-profile relative md:hidden z-40">
            <div class="dropdown-menu">
                <div class="toggle-menu-button-profile w-9 h-9 rounded-full bg-[#1D1B16] text-white flex items-center justify-center font-bold text-sm cursor-pointer shadow-sm">
                    {{ strtoupper(substr($profileName, 0, 1)) }}
                </div>
                <div class="content-dropdown-button-profile absolute bg-white border border-[#EFEDE7] shadow-xl w-48 rounded-2xl py-2 mt-2 right-0">
                    <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="flex items-center px-3 py-2 gap-2 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA]">
                        <i class="fa-solid fa-house"></i>
                        Beranda
                    </a>
                    
                    <a href="{{ route('profile-account-non-school-partner.view', ['role' => Auth::user()->role]) }}" class="flex items-center px-3 py-2 gap-2 text-[13px] text-[#5D5A52] hover:bg-[#F1EFEA]">
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
            <label for="my-drawer-1" aria-label="close sidebar" class="drawer-overlay"></label>

            <div class="bg-white h-full min-w-[75vw] max-w-[85vw] flex flex-col border-r border-[#EFEDE7]">

                {{-- DRAWER HEADER --}}
                <header class="h-16 px-4 bg-white border-b border-[#EFEDE7] flex items-center justify-between">
                    <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" class="h-6 object-contain" alt="Belajar Cerdas">

                    <label for="my-drawer-1" aria-label="close sidebar" class="w-8 h-8 rounded-lg bg-[#F4F2EE] flex items-center justify-center text-[#5D5A52] cursor-pointer">
                        <i class="fas fa-xmark text-lg"></i>
                    </label>
                </header>

                {{-- USER PROFILE CARD --}}
                <div class="p-4 bg-[#F4F2EE] m-3 rounded-2xl flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-[#1D1B16] text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
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
                        <li class="list-item">
                            <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fas fa-house text-[14px] w-5 text-center"></i>
                                <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="link-href text-[13.5px] font-medium">Beranda</a>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-layer-group text-[14px] w-5 text-center"></i>
                                <a href="{{ route('kurikulum.view') }}" class="link-href text-[13.5px] font-medium">Manajemen Kurikulum</a>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-stopwatch text-[14px] w-5 text-center"></i>
                                <a href="{{ route('lms.office.tka-tryout-period-management.view', ['role' => Auth::user()->role]) }}" class="link-href text-[13.5px] font-medium">Tryout TKA</a>
                            </div>
                        </li>
                        
                        <li class="list-item">
                            <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-chart-line text-[14px] w-5 text-center"></i>
                                <a href="{{ route('lms.office.analytics.view', Auth::user()->role) }}" class="link-href text-[13.5px] font-medium">Site Analytics</a>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="content-menu flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                <i class="fa-solid fa-wrench text-[14px] w-5 text-center"></i>
                                <a href="{{ route('lms.office.assessmentDebug.view', Auth::user()->role) }}" class="link-href text-[13.5px] font-medium">Exam Debugger</a>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-book text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">Library</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('library.administrator') }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Library</a>
                                        <a href="{{ route('topik.management') }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Topik Management</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-building-user text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">Office Management</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('lms.officeManagement.manage-user.view', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Manage User</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-book-bookmark text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">Belajar Cerdas LMS</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('lms.contentManagement.view.noSchoolPartner', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Manage Content</a>
                                        <a href="{{ route('lms.questionBankManagement.view.noSchoolPartner', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Manage Question</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-school-flag text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">School Partner</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('lms.schoolSubscription.view', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">LMS</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="list-item">
                            <div class="dropdown-menu w-full flex flex-col items-start">
                                <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2.5 rounded-xl text-[#5D5A52] hover:bg-[#F1EFEA]">
                                    <i class="fa-solid fa-building-columns text-[14px] w-5 text-center"></i>
                                    <span class="text-[13.5px] font-medium">Yayasan</span>
                                    <i class="fas fa-chevron-down absolute right-3 text-[11px] text-[#8E8B84]"></i>
                                </div>

                                <div class="content-dropdown pl-6 pr-2 w-full">
                                    <div class="flex flex-col py-1 space-y-0.5 border-l-2 border-[#EFEDE7] pl-3 my-1">
                                        <a href="{{ route('lms.schoolFoundation.manage.view', ['role' => Auth::user()->role]) }}" class="link-href block py-1.5 text-[12.5px] text-[#5D5A52]">Manajemen Yayasan</a>
                                    </div>
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
