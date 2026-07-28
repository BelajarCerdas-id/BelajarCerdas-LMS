<tr class="hover:bg-gray-50">


<td class="px-4 py-2">-</td>
    {{-- COVER --}}
    <td class="px-4 py-2">
        @php
            $cover = $book->cover;
        @endphp

        @if($cover)
            @if(Str::startsWith($cover,'http'))
                <img src="{{ $cover }}" class="w-16 h-20 object-cover rounded">
            @else
                <img src="{{ asset('library/sampul/'.$cover) }}" class="w-16 h-20 object-cover rounded">
            @endif
        @endif
    </td>

    {{-- JUDUL --}}
    <td class="px-4 py-2 max-w-[200px] truncate">
        {{ $book->title }}
    </td>

    <td class="px-4 py-2 max-w-xs">
                            {{ \Illuminate\Support\Str::limit($book->description ?? '-', 80) }}
                        </td>

    {{-- KELAS --}}
    <td class="px-4 py-2">
        {{ $book->kelas->kelas ?? '-' }}
    </td>

    {{-- MAPEL --}}
    <td class="px-4 py-2">
        {{ $book->mapel->mata_pelajaran ?? '-' }}
    </td>

    {{-- BAB --}}
    <td class="px-4 py-2">
        {{ $book->bab->nama_bab ?? '-' }}
    </td>

    {{-- VIDEO --}}
    <td class="px-4 py-2">
        <a href="{{ $book->file }}"
           target="_blank"
           class="text-blue-500 text-xs">
            Lihat Video
        </a>
    </td>

    {{-- STATUS UPLOAD --}}
    <td class="px-4 py-2">
        <span class="text-green-600 text-xs">
            ✔ Upload selesai
        </span>
    </td>

    {{-- ACTION --}}
    <td class="px-4 py-2">
        <div class="flex gap-2">

            <button
                onclick="openEditModal(
                    '{{ $book->id }}',
                    'video',
                    '{{ addslashes($book->title) }}',
                    '{{ addslashes($book->description) }}',
                    '{{ $book->kelas_id }}',
                    '{{ $book->mapel_id }}',
                    '{{ $book->bab_id ?? '' }}'
                )"
                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">

                Edit

            </button>

            <form action="{{ route('library.delete',$book->id) }}"
                  method="POST">

                @csrf
                @method('DELETE')

                <button
                    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">

                    Delete

                </button>

            </form>

        </div>
    </td>

</tr>