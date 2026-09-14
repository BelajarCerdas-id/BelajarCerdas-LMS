@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5"></div>
    </div>
@elseif(Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-[calc(100%-250px)] min-h-screen bg-white z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5"></div>
    </div>
@elseif(Auth::user()->role === 'Guru')    
    <div class="relative left-0 md:left-62.5 w-[calc(100%-250px)] min-h-screen bg-white z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5"></div>
    </div>
@endif