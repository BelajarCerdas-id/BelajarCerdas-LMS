@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5"></div>
    </div>
@elseif(Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-[calc(100%-250px)] z-20">
        <div class="my-15 mx-7.5"></div>
    </div>
@endif