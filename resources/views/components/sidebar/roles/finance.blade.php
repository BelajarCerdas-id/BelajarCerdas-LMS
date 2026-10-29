@php
    $officeProfile = Auth::user()?->OfficeProfile;
    $profileName = $officeProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Finance';
@endphp
    <aside class="sidebar-beranda-administrator hidden md:flex flex-col h-screen overflow-hidden">
        <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="block shrink-0">
            <div class="logo_details h-16 sm:h-18 flex items-center justify-center px-4 border-b border-white/10">
                <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-9 sm:h-10 w-auto max-w-[190px] object-contain">
            </div>
        </a>

        <ul class="flex-1 overflow-y-auto overflow-x-hidden pb-6 custom-sidebar-scroll">

            <!-- BERANDA -->
            <li class="list-item pb-2">
                <div class="content-menu flex items-center gap-3 px-3 py-2">
                    <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                    <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}"
                        class="link-href text-[14px]">
                        Beranda
                    </a>
                </div>
            </li>
            
            <!-- CONTRACT -->
            <li class="list-item pb-4 px-4">
                <div class="dropdown-menu w-full flex flex-col items-start">

                    <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                        <i class="fa-solid fa-file-signature text-[12px]"></i>
                        <span class="text-[14px]">Contract</span>
                        <i class="fas fa-chevron-down absolute right-0 text-[14px]"></i>
                    </div>

                    <div class="content-dropdown pl-6">

                        <a href="{{ route('lms.finance.manage-contract.view', [
                            'role' => Auth::user()->role
                        ]) }}"
                            class="link-href flex py-2 text-[14px]">
                            Manage Contract
                        </a>

                    </div>
                </div>
            </li>
            
            <!-- REVENUE -->
            <li class="list-item pb-4 px-4">
                <div class="dropdown-menu w-full flex flex-col items-start">

                    <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                        <i class="fa-solid fa-money-bill-trend-up text-[12px]"></i>
                        <span class="text-[14px]">Revenue</span>
                        <i class="fas fa-chevron-down absolute right-0 text-[14px]"></i>
                    </div>

                    <div class="content-dropdown pl-6">

                        <a href="{{ route('lms.finance.manage.revenue.view', [
                            'role' => Auth::user()->role
                        ]) }}"
                            class="link-href flex py-2 text-[14px]">
                            Manage Revenue
                        </a>

                    </div>
                </div>
            </li>
        </ul>
    </aside>

    <div class="relative left-62.5 w-[calc(100%-250px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <div class="w-full h-16 sm:h-18 bg-[#0071BC] shadow-sm flex items-center justify-between px-6 sm:px-8">
                <header class="text-lg sm:text-[19px] font-bold text-white flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-lg cursor-pointer text-white">{!! $backButton !!}</button>
                                    <span class="font-bold text-lg cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-lg cursor-pointer text-white">{!! $backButton !!}</button>
                                <span class="font-bold text-lg cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-lg cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="toggle-menu-button-profile flex items-center gap-3.5 relative cursor-pointer">
                            <div class="flex items-center justify-between gap-2.5 w-52 h-11 rounded-xl px-3 py-1.5 bg-[#005B94] hover:bg-[#004e80] border border-white/15 transition-colors shadow-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-user text-xl text-white opacity-90"></i>
                                    <div class="flex flex-col justify-center">
                                        <span class="text-[12px] text-white font-semibold leading-tight">{{ Str::limit($profileName ?? '', 20) }}</span>
                                        <span class="text-[10px] text-blue-100 font-medium leading-tight mt-0.5">{{ Str::limit(Auth::user()->role ?? '', 20) }}</span>
                                    </div>
                                </div>
                                <i id="rotate-icon" class="fas fa-chevron-down text-white opacity-85 text-xs transition-all duration-400"></i>
                            </div>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-55 rounded-lg mt-2">
                            <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house text-md"></i>
                                    Beranda
                                </div>
                            </a>

                            <a href="{{ route('profile-account-non-school-partner.view', [
                                'role' => Auth::user()->role,
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

                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-[#0071BC]"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
                                </div>
                            </a>

                            <a href="{{ route('profile-account-non-school-partner.view', [
                                'role' => Auth::user()->role,
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
            <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#005B94] font-bold"></i>
                    </div>
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
                            </div>
                        </a>

                        <a href="{{ route('profile-account-non-school-partner.view', [
                            'role' => Auth::user()->role,
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
            <label for="my-drawer-1" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="bg-base-200 min-h-full min-w-[60vw] p-0">
                <header class="w-full h-20 px-4 bg-[#005B94] flex items-center justify-between">
                    <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}">
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="" class="w-26">
                    </a>

                    <label for="my-drawer-1" aria-label="close sidebar">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                    </label>
                </header>

                <div class="profile-account flex flex-col items-center px-2 my-6">
                    <i class="fas fa-circle-user text-5xl text-gray-500"></i>
                    <span>{{ Str::limit($profileName ?? '', 20) }}</span>
                    <span class="text-xs">{{ Auth::user()->role ?? '' }}</span>
                </div>

                <div class="border-b border-gray-300 mb-6"></div>

                <ul class="w-full max-h-screen overflow-y-auto">
                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu">
                            <div class="content-menu text-sm flex items-center gap-3">
                                <i class="fas fa-house"></i>
                                <a href="{{ route('lms.office.dashboard.view', Auth::user()->role) }}" class="link-href flex flex-col text-[13px]">Beranda</a>
                            </div>
                        </div>
                    </li>

                    <!-- CONTRACT -->
                    <li class="list-item pb-4 px-4">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-file-signature text-[12px]"></i>
                                <span class="text-[14px]">Contract</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]"></i>
                            </div>

                            <div class="content-dropdown pl-6">

                                <a href="{{ route('lms.finance.manage-contract.view', [
                                    'role' => Auth::user()->role
                                ]) }}"
                                    class="link-href flex py-2 text-[14px]">
                                    Manage Contract
                                </a>

                            </div>
                        </div>
                    </li>

                    <!-- REVENUE -->
                    <li class="list-item pb-4 px-4">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-money-bill-trend-up text-[12px]"></i>
                                <span class="text-[14px]">Revenue</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]"></i>
                            </div>

                            <div class="content-dropdown pl-6">

                                <a href="{{ route('lms.finance.manage.revenue.view', [
                                    'role' => Auth::user()->role
                                ]) }}"
                                    class="link-href flex py-2 text-[14px]">
                                    Manage Revenue
                                </a>

                            </div>
                        </div>
                    </li>
                </ul>

                <div class="border-b border-gray-300 mb-6"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        class="flex items-center justify-center w-full max-w-62.5 px-4 py-2 mt-6 mx-auto font-bold bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95">
                            <i class="fas fa-right-from-bracket transform"></i>
                            <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
