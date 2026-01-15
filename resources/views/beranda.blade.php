@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role === 'Siswa')

@elseif(Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-[calc(100%-250px)] z-20">
        <div class="my-15 mx-7.5"></div>
    </div>
@endif