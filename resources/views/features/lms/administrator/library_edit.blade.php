{{-- ========================================================================
     EDIT BUKU PERPUSTAKAAN (LibraryController::edit)
     Data: $book, $kelas, $mapels, $babs, $topiks
     Update ke route('library.update', $book->id)
     ======================================================================== --}}
@extends('layouts.app')

@section('title', 'Edit Buku - ' . $book->title)

@section('content')
<div class="p-6 max-w-4xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('library.administrator') }}"
           class="h-10 w-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-500 hover:text-[#0071BC] hover:border-blue-200 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Edit Buku</h1>
            <p class="text-sm text-gray-500">Perbarui data buku: {{ $book->title }}</p>
        </div>
    </div>

    <form action="{{ route('library.update', $book->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        {{-- TIPE --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Materi</label>
            <select name="tipe" required
                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
                @foreach(['buku' => 'Buku (PDF)', 'ppt' => 'PPT', 'lks' => 'LKS', 'video' => 'Video'] as $value => $label)
                    <option value="{{ $value }}" {{ $book->tipe === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- JUDUL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Judul</label>
            <input type="text" name="title" value="{{ old('title', $book->title) }}" required
                   class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
        </div>

        {{-- DESKRIPSI --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">{{ old('description', $book->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- KELAS --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                <select name="kelas_id" required
                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
                    <option value="">Pilih Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $book->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->kelas }}</option>
                    @endforeach
                </select>
            </div>

            {{-- MAPEL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="mapel_id" required
                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
                    <option value="">Pilih Mapel</option>
                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}" {{ $book->mapel_id == $m->id ? 'selected' : '' }}>{{ $m->mata_pelajaran }}</option>
                    @endforeach
                </select>
            </div>

            {{-- BAB --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bab</label>
                <select name="bab_id"
                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
                    <option value="">Pilih Bab</option>
                    @foreach($babs as $b)
                        <option value="{{ $b->id }}" {{ $book->bab_id == $b->id ? 'selected' : '' }}>{{ $b->nama_bab }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- TOPIK --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Topik Materi</label>
            <select name="topik_materi_id"
                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition">
                <option value="">Tanpa Topik</option>
                @foreach($topiks as $t)
                    <option value="{{ $t->id }}" {{ $book->topik_materi_id == $t->id ? 'selected' : '' }}>{{ $t->nama_topik }}</option>
                @endforeach
            </select>
        </div>

        {{-- GANTI FILE --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Ganti File (opsional)</label>
                <input type="file" name="file"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-[#0071BC] file:font-semibold file:cursor-pointer">
                @if($book->file)
                    <p class="text-xs text-gray-400 mt-1">File saat ini: {{ $book->file }}</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Ganti Cover (opsional)</label>
                <input type="file" name="cover" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-[#0071BC] file:font-semibold file:cursor-pointer">
                @if($book->cover)
                    <p class="text-xs text-gray-400 mt-1">Cover saat ini: {{ $book->cover }}</p>
                @endif
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('library.administrator') }}"
               class="h-11 px-5 inline-flex items-center rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="h-11 px-6 inline-flex items-center gap-2 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white text-sm font-semibold shadow-sm transition cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>
@endsection
