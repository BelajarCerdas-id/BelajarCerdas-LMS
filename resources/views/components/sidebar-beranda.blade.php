<x-script></x-script>

@if (Auth::user()->role === 'Siswa')

@elseif(Auth::user()->role === 'Administrator')
    <aside class="sidebar-beranda-administrator hidden md:block">
        <a href="{{ route('beranda') }}">
            <div class="logo_details flex items-center justify-center">
                <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="" class="w-50 h-32">
            </div>
        </a>
        <ul class="max-h-screen overflow-y-auto">
            <li class="list-item m-2 pb-3">
                <div class="dropdown-menu">
                    <div class="content-menu text-sm flex items-center gap-3">
                        <i class="fas fa-house"></i>
                        <a href="{{ route('beranda') }}" class="link-href flex flex-col text-[13px]">Beranda</a>
                    </div>

                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-school-flag text-[12px]"></i>
                                <span class="text-[14px]">School Partner</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate"></i>
                            </div>
                            <div class="content-dropdown">
                                <a href="{{ route('lms.schoolSubscription.view') }}" class="link-href flex flex-col px-2 py-2 text-[13px]">LMS</a>
                            </div>
                        </div>
                    </li>
                </div>
            </li>
        </ul>
    </aside>

    <div class="relative left-62.5 w-[calc(100%-250px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <!-- Navbar for PC -->
            <div class="w-full h-24 bg-white shadow-lg flex items-center justify-between px-12.5">
                <header class="text-[20px] font-bold opacity-70 flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                    <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="toggle-menu-button-profile flex items-center gap-3.5 relative cursor-pointer">
                            <div class="flex items-center justify-between gap-2.5 w-55 h-14 rounded-[20px] p-2.5 bg-[#005B94]">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-user text-2xl text-white opacity-85"></i>
                                    <div class="flex flex-col">
                                        <span class="text-[12px] text-white font-semibold leading-6">{{ Str::limit(Auth::user()->OfficeProfile->nama_lengkap ?? '', 20) }}</span>
                                        <span class="text-[11px] text-white font-semibold leading-6">{{ Str::limit(Auth::user()->role ?? '', 20) }}</span>
                                    </div>
                                </div>
                                <i id="rotate" class="fas fa-chevron-down text-white opacity-85 transition-all duration-400"></i>
                            </div>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-55 rounded-lg mt-2">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house text-md"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- profile button rounded mobile -->
                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-[#005B94]"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--- Sidebar Beranda for mobile ---->
    <nav class="navbar-beranda-phone w-full h-20 flex justify-between items-center md:hidden bg-white shadow-lg px-6">
        <div class="flex items-center h-full">
            <label for="my-drawer-1">
                <i class="fas fa-bars text-2xl relative top-1 cursor-pointer"></i>
            </label>
            <a href="{{ route('beranda') }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <!-- profile button rounded -->
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#005B94] font-bold"></i>
                    </div>
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('beranda') }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
                            </div>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
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
                    <a href="{{ route('beranda') }}">
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="" class="w-26">
                    </a>

                    <label for="my-drawer-1" aria-label="close sidebar">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer" onclick="togglePopup()"></i>
                    </label>
                </header>

                <div class="profile-account flex flex-col items-center px-2 my-6">
                    <i class="fas fa-circle-user text-5xl text-gray-500"></i>
                    <span>{{ Str::limit(Auth::user()->OfficeProfile->nama_lengkap ?? '', 20) }}</span>
                    <span class="text-xs">{{ Auth::user()->role ?? '' }}</span>
                </div>

                <div class="border-b border-gray-300 mb-6"></div>

                <!-- Sidebar content here -->
                <ul class="w-full max-h-screen overflow-y-auto">
                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu">
                            <div class="content-menu text-sm flex items-center gap-3">
                                <i class="fas fa-house"></i>
                                <a href="{{ route('beranda') }}" class="link-href flex flex-col text-[13px]">Beranda</a>
                            </div>
                        </div>
                    </li>

                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-school-flag text-[12px]"></i>
                                <span class="text-[14px]">School Partner</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate"></i>
                            </div>
                            <div class="content-dropdown">
                                <a href="{{ route('lms.schoolSubscription.view') }}" class="link-href flex flex-col px-2 py-2 text-[13px]">LMS</a>
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
@else
    <p>You do not have access to this dashboard.</p>
@endif

<!-- COMPONENTS -->
<script src="{{ asset('assets/js/components/sidebar-administrator.js') }}"></script> <!-- sidebar administrator -->
<script src="{{ asset('assets/js/components/navbar-button-profile.js') }}"></script> <!-- button profile user in navbar -->
