<x-script></x-script>

@php
    $isAuthenticated = Auth::check();
    $isAdministrator = $isAuthenticated && in_array(strtolower((string) Auth::user()->role), ['administrator', 'admin'], true);
    $isStudentOrTeacher = $isAuthenticated && in_array(Auth::user()->role, ['Siswa', 'Guru'], true);
    $contentWrapperClass = 'relative left-0 w-full z-20';

    if ($isAdministrator) {
        $contentWrapperClass = 'relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen';
    } elseif ($isStudentOrTeacher) {
        $contentWrapperClass = 'relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen';
    }
@endphp

@if ($isAuthenticated)
    @include('components/sidebar-beranda', ['headerSideNav' => 'Public Library'])

    <div class="{{ $contentWrapperClass }}">
        <div class="my-15 mx-4 md:mx-7.5">
            @include('public-library.partials.library-content', ['isAuthenticated' => true, 'isAdministrator' => $isAdministrator])
        </div>
    </div>
@else
    <div class="min-h-screen bg-slate-100">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @include('public-library.partials.library-content', ['isAuthenticated' => false, 'isAdministrator' => false])
        </div>
    </div>
@endif
