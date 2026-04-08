{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
    'headerSideNav' => 'Library',
])

@if (Auth::check() && Auth::user()->role === 'Siswa')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
    <div class="my-15 mx-7.5">
        <main>

            <!-- HEADER -->
            <section class="mb-10">
                <h1 class="text-2xl font-bold opacity-80">Perpustakaan Digital</h1>
                <p class="text-gray-500 mt-1">Pilih mata pelajaran untuk melihat buku</p>
                <hr class="border-gray-300 mt-4 w-60">
            </section>

            <!-- GRID MAPEL -->
            <section>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

                    @php
                        $mapel_colors = [
                            'Bahasa Arab' => ['bg' => 'bg-green-100','icon' => 'fa-book'],
                            'Bahasa Indonesia' => ['bg' => 'bg-red-100','icon' => 'fa-book-open'],
                            'Bahasa Inggris' => ['bg' => 'bg-purple-100','icon' => 'fa-language'],
                            'Jawa' => ['bg' => 'bg-yellow-100','icon' => 'fa-book'],
                            'Biologi' => ['bg' => 'bg-green-200','icon' => 'fa-flask'],
                            'Fisika' => ['bg' => 'bg-blue-200','icon' => 'fa-atom'],
                            'IPA Terpadu' => ['bg' => 'bg-teal-200','icon' => 'fa-flask'],
                            'IPAS' => ['bg' => 'bg-indigo-200','icon' => 'fa-flask'],
                            'Matematika' => ['bg' => 'bg-blue-100','icon' => 'fa-calculator'],
                            'Kimia' => ['bg' => 'bg-pink-200','icon' => 'fa-vial']
                        ];
                    @endphp

                    @foreach($mapels->unique('mata_pelajaran') as $mapel)
                        @php
                            $color = $mapel_colors[$mapel->mata_pelajaran]['bg'] ?? 'bg-gray-100';
                            $icon = $mapel_colors[$mapel->mata_pelajaran]['icon'] ?? 'fa-book';
                        @endphp

                        <a href="{{ route('student.library.mapel', $mapel->id) }}" class="group">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                                <div class="h-40 {{ $color }} flex items-center justify-center">
                                    <i class="fa-solid {{ $icon }} text-5xl text-gray-600"></i>
                                </div>

                                <div class="p-4">
                                    <h2 class="font-semibold text-lg">{{ $mapel->mata_pelajaran }}</h2>
                                    <p class="text-sm text-gray-500">
                                        {{ $mapel->deskripsi ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </a>

                    @endforeach

                </div>
            </section>

        </main>
    </div>
</div>

@else
<div class="flex flex-col min-h-screen items-center justify-center">
    <p class="text-xl font-bold">ALERT</p>
    <p class="text-gray-500">You do not have access to this page</p>
</div>
@endif