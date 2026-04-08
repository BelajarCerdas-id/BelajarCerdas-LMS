{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
    'headerSideNav' => 'Library',
])

@if (Auth::check() && Auth::user()->role === 'Siswa')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="my-15 mx-7.5">

        <main>

            <!-- HEADER MAPEL -->
            <section class="mb-10">
                <h1 class="text-2xl font-bold opacity-80">{{ $mapel->mata_pelajaran }}</h1>
                <p class="text-gray-500 mt-1">Materi dan tugas mata pelajaran {{ $mapel->mata_pelajaran }}</p>
                <hr class="border-gray-300 mt-4 w-60">
            </section>

            <!-- LOOP BAB -->
            @forelse($chapters as $chapterName => $books)
            <section class="mb-12">

                <!-- JUDUL BAB -->
                <h2 class="text-xl font-semibold mb-6 text-gray-700">{{ $chapterName }}</h2>

                <!-- GRID BUKU -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                    @foreach($books as $book)
                    <a href="{{ route('student.library.read', $book->id) }}">
                        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden">

                            <!-- COVER -->
                            <div class="h-56 bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($book->cover)
                                    <img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-400 text-sm">No Cover</span>
                                @endif
                            </div>

                            <!-- CONTENT -->
                            <div class="p-4 space-y-2">

                                <!-- TYPE -->
                                <span class="text-xs px-2 py-1 rounded 
                                    {{ $book->type == 'task' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                    {{ $book->type == 'task' ? 'Tugas' : 'Baca' }}
                                </span>

                                <!-- TITLE -->
                                <h2 class="font-semibold text-lg leading-tight">{{ $book->title }}</h2>

                                <!-- INFO KELAS -->
                                <p class="text-xs text-gray-500">Kelas {{ $book->kelas->kelas ?? '-' }}</p>

                                <!-- DESCRIPTION -->
                                <p class="text-sm text-gray-600 line-clamp-3">{{ $book->description }}</p>

                            </div>

                        </div>
                    </a>
                    @endforeach

                </div>

            </section>
            @empty
            <div class="w-full h-60 flex items-center justify-center text-gray-400">
                Belum ada buku untuk mapel ini
            </div>
            @endforelse

        </main>

    </div>
</div>

@else
<div class="flex flex-col min-h-screen items-center justify-center">
    <p class="text-xl font-bold">ALERT</p>
    <p class="text-gray-500">You do not have access to this page</p>
</div>
@endif