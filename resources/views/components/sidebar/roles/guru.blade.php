@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $schoolPartner = $staffProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'Sekolah Masa';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Guru';
    $userInitials = collect(explode(' ', trim($profileName)))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

    {{-- ============================================================ --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================ --}}
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72 bg-surface border-r border-dpai min-h-screen text-dpai-primary" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-primary);">

        {{-- BRAND & IDENTITAS SEKOLAH --}}
        <div class="h-20 shrink-0 flex items-center border-b border-dpai px-5 py-3 relative group" style="border-color: var(--color-border);">
            <div class="flex items-center gap-3">
                @if(!empty($schoolLogo))
                    <div id="logo-wrapper" class="w-10 h-10 flex items-center justify-center overflow-hidden shrink-0" style="border-radius: var(--radius-tile);">
                        <img id="school-logo-preview" src="{{ asset($schoolLogo) }}" alt="{{ $schoolName }}" class="max-h-10 max-w-full object-contain">
                    </div>
                @else
                    <div class="w-10 h-10 flex items-center justify-center shrink-0 font-extrabold text-sm text-white" style="border-radius: var(--radius-tile); background: var(--color-ink);">
                        <span>{{ substr($schoolName, 0, 2) }}</span>
                    </div>
                @endif
                <div class="flex flex-col min-w-0">
                    <span class="text-[14.5px] font-extrabold leading-tight tracking-tight truncate text-dpai-primary">
                        {{ $schoolName }}
                    </span>
                    <span class="text-[11px] font-medium text-dpai-muted truncate">
                        Tahun Ajaran 2025/2026
                    </span>
                </div>
            </div>
        </div>

        {{-- NAVIGASI UTAMA SIDEBAR --}}
        <div class="flex-1 overflow-y-auto px-3 py-4">

            <ul class="space-y-1">

                {{-- KATEGORI: MENU GURU --}}
                <p class="px-3 pt-2 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Menu Guru
                </p>

                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <a href="{{ route('lms.teacher.view', [
                                'role' => Auth::user()->role, 
                                'schoolName' => $schoolName, 
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Beranda
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: PEMBELAJARAN --}}
                <p class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Pembelajaran
                </p>

                {{-- MODUL PEMBELAJARAN --}}
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                                <i class="fa-solid fa-book-open"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Modul Pembelajaran</span>
                            <i class="fas fa-chevron-down text-[11px] text-dpai-muted transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.teacherContentManagement.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Katalog Modul
                                </a>
                                <a href="{{ route('lms.teacherContentForRelease.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Rilis Modul
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- BANK SOAL --}}
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                                <i class="fa-solid fa-layer-group"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Bank Soal</span>
                            <i class="fas fa-chevron-down text-[11px] text-dpai-muted transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.teacherQuestionBankManagement.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Daftar Bank Soal
                                </a>
                                <a href="{{ route('lms.teacherQuestionBankForRelease.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Rilis Bank Soal
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- ASESMEN --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </span>
                        <a href="{{ route('lms.teacherAssessmentManagement.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Asesmen
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: AKADEMIK & NILAI --}}
                <p class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Akademik & Nilai
                </p>

                {{-- PENILAIAN --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                        <a href="{{ route('lms.assessmentGradingManagement.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Penilaian
                        </a>
                    </div>
                </li>

                {{-- BUKU NILAI --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-table-list"></i>
                        </span>
                        <a href="{{ route('lms.teacherClassList.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Buku Nilai
                        </a>
                    </div>
                </li>

                {{-- LEGER & TRANSKRIP --}}
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                                <i class="fa-solid fa-file-excel"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Leger & Transkrip</span>
                            <i class="fas fa-chevron-down text-[11px] text-dpai-muted transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.teacherClassListGradeLedger.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Leger Nilai
                                </a>
                                <a href="{{ route('lms.teacherClassListAcademicTranscript.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Transkrip Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- KATEGORI: AKTIVITAS --}}
                <p class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Aktivitas
                </p>

                {{-- PRESENSI SISWA --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-user-check"></i>
                        </span>
                        <a href="{{ route('lms.teacherClassListSubjectAttendance.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Presensi Siswa
                        </a>
                    </div>
                </li>

                {{-- AGENDA HARIAN --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </span>
                        <a href="{{ route('lms.teacherDailyAgenda.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}" class="link-href flex-1 text-[13.5px]">
                            Agenda Harian
                        </a>
                    </div>
                </li>

                {{-- KATEGORI: ADMINISTRASI --}}
                <p class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Administrasi
                </p>

                {{-- PERANGKAT KURIKULUM --}}
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                                <i class="fa-solid fa-folder-closed"></i>
                            </span>
                            <span class="flex-1 text-[13.5px]">Perangkat Kurikulum</span>
                            <i class="fas fa-chevron-down text-[11px] text-dpai-muted transition-transform duration-200"></i>
                        </div>

                        <div class="content-dropdown pl-10 pr-2 w-full">
                            <div class="flex flex-col py-1 space-y-0.5 border-l-2 pl-3 my-1" style="border-color: var(--color-border);">
                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Analisis CP hingga ATP
                                </a>
                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    PROTA & PROSEM
                                </a>
                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    RPP & RPPM
                                </a>
                                <a href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId
                                    ]) }}" class="link-href block py-1.5 px-2 text-[12.5px] text-dpai-secondary transition" style="border-radius: var(--radius-sm);">
                                    Refleksi Guru
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- KATEGORI: INFORMASI --}}
                <p class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">
                    Informasi
                </p>

                {{-- JADWAL & KALENDER --}}
                <li class="list-item {{ request()->routeIs('lms.teacherScheduleCalendar.*') ? 'active' : '' }}">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl transition font-medium text-[13.5px] {{ request()->routeIs('lms.teacherScheduleCalendar.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold active' : 'text-dpai-secondary hover:text-dpai-primary hover:bg-[#F1EFEA]' }}" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px] {{ request()->routeIs('lms.teacherScheduleCalendar.*') ? 'bg-white text-dpai-accent shadow-xs' : '' }}" style="border-radius: var(--radius-tile); {{ !request()->routeIs('lms.teacherScheduleCalendar.*') ? 'background: var(--color-surface-muted); color: var(--color-text-secondary);' : '' }}">
                            <i class="fa-solid fa-calendar-days"></i>
                        </span>
                        <a href="{{ route('lms.teacherScheduleCalendar.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex-1 text-[13.5px] {{ request()->routeIs('lms.teacherScheduleCalendar.*') ? 'active text-dpai-accent-text font-bold' : '' }}">
                            Jadwal & Kalender
                        </a>
                    </div>
                </li>

                {{-- POLLING --}}
                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary transition font-medium text-[13.5px]" style="border-radius: var(--radius-nav-item);">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0 text-[13px]" style="border-radius: var(--radius-tile); background: var(--color-surface-muted); color: var(--color-text-secondary);">
                            <i class="fa-solid fa-square-poll-vertical"></i>
                        </span>
                        <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex-1 text-[13.5px]">
                            Polling
                        </a>
                    </div>
                </li>
            </ul>
        </div>

        {{-- FOOTER PARTNERSHIP (STICKY DI BAWAH) --}}
        <div class="p-3 border-t border-dpai shrink-0 sticky bottom-0 z-10" style="border-color: var(--color-border); background: var(--color-surface);">
            <div class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl" style="border-radius: var(--radius-lg); background: var(--color-bg-page);">
                <span class="text-[10px] font-bold text-dpai-muted tracking-wide uppercase">
                    Partnership By:
                </span>
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-5 object-contain">
            </div>
        </div>
    </aside>

    {{-- ============================================================ --}}
    {{-- DESKTOP TOP HEADER / NAVBAR (MATCHING LMS-SHELL LAYOUT) --}}
    {{-- ============================================================ --}}
    <div class="relative left-72.5 w-[calc(100%-290px)] transition-all duration-500 ease-in-out hidden md:block z-30">
        <header class="w-full h-16 border-b border-dpai flex items-center justify-between px-6 sm:px-8 sticky top-0" style="background: rgba(244, 242, 238, 0.95); backdrop-filter: blur(10px); border-color: var(--color-border);">
            
            {{-- BREADCRUMB --}}
            <div class="flex items-center gap-2 text-[12.5px] text-dpai-muted font-medium min-w-0">
                <span>{{ $schoolName }}</span>
                <i class="fa-solid fa-chevron-right text-[10px] text-dpai-muted"></i>
                <b class="text-dpai-primary font-bold truncate">{{ $headerSideNav ?? 'Beranda Guru' }}</b>
            </div>

            {{-- SEARCH, NOTIFICATION & PROFILE --}}
            <div class="flex items-center gap-3">
                
                {{-- SEARCH BAR --}}
                <label class="flex items-center gap-2 px-3.5 py-1.5 w-64 bg-surface border border-dpai text-dpai-muted" style="background: var(--color-surface); border-color: var(--color-border); border-radius: var(--radius-badge);">
                    <i class="fa-solid fa-magnifying-glass text-[13px] text-dpai-muted"></i>
                    <input type="search" placeholder="Cari modul, soal, atau murid" class="w-full bg-transparent border-0 outline-none text-[12.5px] text-dpai-primary placeholder-dpai-muted" />
                </label>

                {{-- NOTIFICATION ICON BUTTON --}}
                <button type="button" aria-label="Notifikasi" class="w-9 h-9 flex items-center justify-center bg-surface border border-dpai relative cursor-pointer text-dpai-secondary hover:text-dpai-primary transition" style="background: var(--color-surface); border-color: var(--color-border); border-radius: 50%;">
                    <i class="fa-solid fa-bell text-[14px]"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full border border-white" style="background: var(--color-accent);" aria-hidden="true"></span>
                </button>

                {{-- PROFILE AREA --}}
                <div class="list-item-button-profile relative z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile flex items-center gap-2.5 py-1 px-2.5 bg-surface border border-dpai cursor-pointer transition shadow-xs" style="background: var(--color-surface); border-color: var(--color-border); border-radius: var(--radius-badge);">
                            
                            {{-- USER INITIALS AVATAR --}}
                            <span class="w-8 h-8 rounded-full text-white flex items-center justify-center font-extrabold text-xs" style="background: var(--color-ink);">
                                {{ $userInitials }}
                            </span>

                            <div class="flex flex-col text-left leading-tight pr-1">
                                <span class="text-[12.5px] font-bold text-dpai-primary truncate max-w-[130px]">
                                    {{ $profileName }}
                                </span>
                                <span class="text-[10.5px] font-medium text-dpai-muted">
                                    {{ Auth::user()->role ?? 'Guru' }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-dpai-muted"></i>
                        </div>

                        {{-- DROPDOWN MENU --}}
                        <div class="content-dropdown hidden absolute right-0 mt-2 w-48 bg-surface border border-dpai shadow-lg py-1.5 z-50" style="background: var(--color-surface); border-color: var(--color-border); border-radius: var(--radius-card); box-shadow: var(--shadow-dropdown);">
                            <a href="{{ route('profile-account-school-partner.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-dpai-secondary hover:bg-surface-muted hover:text-dpai-primary">
                                <i class="fa-solid fa-user-circle w-4"></i> Profil Saya
                            </a>
                            <div class="my-1 border-t border-dpai" style="border-color: var(--color-divider);"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-left" style="color: var(--color-danger);">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>

    {{-- ============================================================ --}}
    {{-- MOBILE DRAWER / SIDEBAR --}}
    {{-- ============================================================ --}}
    <div class="block md:hidden">
        {{-- Mobile Top Bar --}}
        <header class="w-full h-14 bg-surface border-b border-dpai px-4 flex items-center justify-between sticky top-0 z-30" style="background: var(--color-surface); border-color: var(--color-border);">
            <div class="flex items-center gap-2">
                <button type="button" id="mobile-sidebar-toggle" aria-label="Buka Menu" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface-muted text-dpai-secondary" style="border-radius: var(--radius-btn-sm); background: var(--color-surface-muted);">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
                <b class="text-sm font-extrabold text-dpai-primary truncate">{{ $headerSideNav ?? 'Beranda Guru' }}</b>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-xs" style="background: var(--color-ink);">
                    {{ $userInitials }}
                </span>
            </div>
        </header>

        {{-- Mobile Drawer Overlay & Container --}}
        <div id="mobile-sidebar-drawer" class="fixed inset-0 z-50 hidden">
            <div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"></div>
            <aside class="sidebar-beranda-student fixed top-0 bottom-0 left-0 w-72 max-w-[85vw] bg-surface flex flex-col justify-between overflow-y-auto z-50 shadow-2xl" style="background: var(--color-surface);">
                
                {{-- Drawer Header --}}
                <div class="p-4 border-b border-dpai flex items-center justify-between" style="border-color: var(--color-border);">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl text-white flex items-center justify-center font-black text-sm" style="border-radius: var(--radius-tile); background: var(--color-ink);">
                            {{ substr($schoolName, 0, 2) }}
                        </span>
                        <div class="leading-tight">
                            <span class="text-sm font-extrabold text-dpai-primary block truncate max-w-[150px]">{{ $schoolName }}</span>
                            <span class="text-[11px] text-dpai-muted font-medium">Guru</span>
                        </div>
                    </div>
                    <button type="button" id="mobile-sidebar-close" aria-label="Tutup Menu" class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-muted text-dpai-muted" style="background: var(--color-surface-muted);">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- Drawer Links --}}
                <div class="flex-1 overflow-y-auto px-3 py-3">
                    <ul class="space-y-1">
                        <p class="px-3 pt-1 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">Menu Guru</p>
                        <li>
                            <a href="{{ route('lms.teacher.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-house w-5 text-center"></i> Beranda
                            </a>
                        </li>

                        <p class="px-3 pt-3 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">Pembelajaran</p>
                        <li>
                            <a href="{{ route('lms.teacherContentManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-book-open w-5 text-center"></i> Modul Pembelajaran
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherQuestionBankManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-layer-group w-5 text-center"></i> Bank Soal
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherAssessmentManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-clipboard-list w-5 text-center"></i> Asesmen
                            </a>
                        </li>

                        <p class="px-3 pt-3 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">Akademik & Nilai</p>
                        <li>
                            <a href="{{ route('lms.assessmentGradingManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-pen-to-square w-5 text-center"></i> Penilaian
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherClassList.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-table-list w-5 text-center"></i> Buku Nilai
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherClassListGradeLedger.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-file-excel w-5 text-center"></i> Leger & Transkrip
                            </a>
                        </li>

                        <p class="px-3 pt-3 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">Aktivitas</p>
                        <li>
                            <a href="{{ route('lms.teacherClassListSubjectAttendance.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-user-check w-5 text-center"></i> Presensi Siswa
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherDailyAgenda.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-dpai-secondary text-[13px] font-medium" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-book-bookmark w-5 text-center"></i> Agenda Harian
                            </a>
                        </li>

                        <p class="px-3 pt-3 pb-1 text-[10.5px] font-bold text-dpai-muted uppercase tracking-wider">Informasi</p>
                        <li>
                            <a href="{{ route('lms.teacherScheduleCalendar.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-[13px] font-medium {{ request()->routeIs('lms.teacherScheduleCalendar.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold active' : 'text-dpai-secondary' }}" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-calendar-days w-5 text-center"></i> Jadwal & Kalender
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="link-href flex items-center gap-3 px-3 py-2 rounded-xl text-[13px] font-medium {{ request()->routeIs('lms.teacherPolling.*') ? 'bg-dpai-accent-soft text-dpai-accent-text font-bold active' : 'text-dpai-secondary' }}" style="border-radius: var(--radius-nav-item);">
                                <i class="fa-solid fa-square-poll-vertical w-5 text-center"></i> Polling
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Drawer Logout Button --}}
                <div class="p-4 border-t border-dpai" style="border-color: var(--color-border);">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 px-4 font-bold text-sm gap-2 cursor-pointer transition active:scale-95" style="border-radius: var(--radius-btn); background: var(--color-danger-soft); color: var(--color-danger);">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
