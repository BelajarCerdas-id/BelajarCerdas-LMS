<tr class="hover:bg-slate-50/80 transition-colors">
    <td class="px-4 py-3 text-center text-gray-500 font-medium text-xs">-</td>
    {{-- COVER --}}
    <td class="px-4 py-3">
        @php
            $cover = $book->cover;
        @endphp

        @if($cover)
            @if(Str::startsWith($cover,'http'))
                <img src="{{ $cover }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
            @else
                <img src="{{ asset('library/sampul/'.$cover) }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
            @endif
        @else
            <div class="w-14 h-18 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Cover</div>
        @endif
    </td>

    {{-- JUDUL --}}
    <td class="px-4 py-3 font-semibold text-gray-800 max-w-[200px] truncate text-sm">
        {{ $book->title }}
    </td>

    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">
        {{ \Illuminate\Support\Str::limit($book->description ?? '-', 80) }}
    </td>

    {{-- KELAS --}}
    <td class="px-4 py-3 text-gray-600 font-medium text-xs">
        {{ $book->kelas->kelas ?? '-' }}
    </td>

    {{-- MAPEL --}}
    <td class="px-4 py-3 text-gray-600 font-medium text-xs">
        {{ $book->mapel->mata_pelajaran ?? '-' }}
    </td>

    {{-- BAB --}}
    <td class="px-4 py-3 text-gray-600 font-medium text-xs">
        {{ $book->bab->nama_bab ?? '-' }}
    </td>

    {{-- VIDEO --}}
    <td class="px-4 py-3 text-center">
        <a href="{{ $book->file }}"
           target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0071BC] bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
            <i class="fa-solid fa-play text-[11px]"></i>
            <span>Lihat</span>
        </a>
    </td>

    {{-- STATUS UPLOAD --}}
    <td class="px-4 py-3 text-center">
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200/60">
            <i class="fa-solid fa-circle-check text-[10px]"></i>
            <span>Selesai</span>
        </span>
    </td>

    {{-- ACTION --}}
    <td class="px-4 py-3 text-center">
        <div class="flex items-center justify-center gap-2">
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
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>Edit</span>
            </button>

            <form action="{{ route('library.delete',$book->id) }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <button
                    onclick="return confirm('Hapus video ini?')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition-colors cursor-pointer">
                    <i class="fa-solid fa-trash text-[11px]"></i>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </td>
</tr>