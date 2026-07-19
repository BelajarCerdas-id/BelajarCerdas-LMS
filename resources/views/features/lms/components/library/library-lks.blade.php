    {{-- SIDEBAR --}}
    @include('components/sidebar-beranda', [
        'headerSideNav' => 'LKPD Library',
    ])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">
    <div class="mx-7.5 mt-6 mb-4">
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-2 text-lg font-semibold hover:text-blue-600 transition">
            <i class="fa-solid fa-chevron-left text-sm"></i>
            <span>kembali</span>
        </a>
    </div>
</div>
    @if (Auth::check() && in_array(Auth::user()->role, ['Siswa', 'Guru']))

    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>

                <!-- HEADER -->
                <section class="mb-10">
                    <h1 class="text-2xl font-bold opacity-80">Perpustakaan Digital</h1>
                    <p class="text-gray-500 mt-1">Pilih mata pelajaran untuk melihat LKPD</p>
                    <hr class="border-gray-300 mt-4 w-60">
                </section>

                <section id="kelas_section" class="space-y-10">

    @php
    $sd = $kelas->filter(function($k){
        $no = (int) filter_var($k->kelas, FILTER_SANITIZE_NUMBER_INT);
        return $no >= 1 && $no <= 6;
    });

    $smp = $kelas->filter(function($k){
        $no = (int) filter_var($k->kelas, FILTER_SANITIZE_NUMBER_INT);
        return $no >= 7 && $no <= 9;
    });

    $sma = $kelas->filter(function($k){
        $no = (int) filter_var($k->kelas, FILTER_SANITIZE_NUMBER_INT);
        return $no >= 10 && $no <= 12;
    });
@endphp

    {{-- SD --}}
    <div>
        <h2 class="text-2xl font-bold mb-5 border-l-4 border-blue-500 pl-3">
            SD
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">

            @foreach($sd as $k)

            <div
                class="kelas-card cursor-pointer bg-white rounded-xl shadow-md hover:shadow-xl transition p-6 text-center"
                onclick="selectKelas({{ $k->id }})">

                <div class="text-3xl mb-3">🏫</div>

                <h2 class="font-semibold text-lg">
                    Kelas {{ $k->kelas }}
                </h2>

                <p class="text-sm text-gray-500 mt-2">
                    Klik untuk lihat mata pelajaran
                </p>

            </div>

            @endforeach

        </div>
    </div>

    {{-- SMP --}}
    <div>
        <h2 class="text-2xl font-bold mb-5 border-l-4 border-green-500 pl-3">
            SMP
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-6">

            @foreach($smp as $k)

            <div
                class="kelas-card cursor-pointer bg-white rounded-xl shadow-md hover:shadow-xl transition p-6 text-center"
                onclick="selectKelas({{ $k->id }})">

                <div class="text-3xl mb-3">🏫</div>

                <h2 class="font-semibold text-lg">
                    Kelas {{ $k->kelas }}
                </h2>

                <p class="text-sm text-gray-500 mt-2">
                    Klik untuk lihat mata pelajaran
                </p>

            </div>

            @endforeach

        </div>
    </div>

    {{-- SMA/SMK --}}
    <div>
        <h2 class="text-2xl font-bold mb-5 border-l-4 border-purple-500 pl-3">
            SMA / SMK
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-6">

            @foreach($sma as $k)

            <div
                class="kelas-card cursor-pointer bg-white rounded-xl shadow-md hover:shadow-xl transition p-6 text-center"
                onclick="selectKelas({{ $k->id }})">

                <div class="text-3xl mb-3">🏫</div>

                <h2 class="font-semibold text-lg">
                    Kelas {{ $k->kelas }}
                </h2>

                <p class="text-sm text-gray-500 mt-2">
                    Klik untuk lihat mata pelajaran
                </p>

            </div>

            @endforeach

        </div>
    </div>

</section>
                <!-- GRID MAPEL -->
                <section id="mapel_section" class="hidden mt-10">
                    <div id="mapel_container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

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

                            <a href="{{ route('student.library.lks.detail', $mapel->id) }}" class="group">
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

    <script>
        function selectKelas(kelasId) {

    fetch(`/student/library/lks/mapel?kelas_id=${kelasId}`)
        .then(res => res.json())
        .then(data => {

            const container = document.getElementById('mapel_container');
            container.innerHTML = '';

            data.forEach(mapel => {

                container.innerHTML += `
                    <a href="/student/library/lks/${mapel.id}" class="group">
                        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition overflow-hidden">

                            <div class="h-40 bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-book text-5xl text-gray-600"></i>
                            </div>

                            <div class="p-4">
                                <h2 class="font-semibold text-lg">
                                    ${mapel.mata_pelajaran}
                                </h2>
                            </div>

                        </div>
                    </a>
                `;
            });

            document.getElementById('kelas_section').classList.add('hidden');
            document.getElementById('mapel_section').classList.remove('hidden');
        });
}
    </script>