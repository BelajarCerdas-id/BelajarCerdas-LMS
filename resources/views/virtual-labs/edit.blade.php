<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Virtual Lab Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-gray-50">
    <div class="py-8 px-4 md:px-8">
        <div class="mb-8">
            <a href="{{ route('virtual-labs.manage') }}" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-semibold mb-4"><i class="fa-solid fa-arrow-left"></i><span>Kembali</span></a>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Virtual Lab</h1>
        </div>

        <form action="{{ route('virtual-labs.update', $lab->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl p-8">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul *</label>
                        <input type="text" name="title" value="{{ $lab->title }}" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi *</label>
                        <textarea name="description" rows="4" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">{{ $lab->description }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mata Pelajaran *</label>
                        <select name="subject" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                            @foreach(['Fisika' => 'Fisika', 'Kimia' => 'Kimia', 'Biologi' => 'Biologi'] as $val)
                                <option value="{{ $val }}" {{ $lab->subject == $val ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kurikulum *</label>
                        <select name="kurikulum_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                            @foreach($kurikulums as $kur)<option value="{{ $kur->id }}" {{ $lab->kurikulum_id == $kur->id ? 'selected' : '' }}>{{ $kur->nama_kurikulum }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kelas *</label>
                        <select name="kelas_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                            @foreach($kelasList as $kelas)<option value="{{ $kelas->id }}" {{ $lab->kelas_id == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mapel *</label>
                        <select name="mapel_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                            @foreach($mapels as $mapel)<option value="{{ $mapel->id }}" {{ $lab->mapel_id == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Video URL</label>
                        <input type="text" name="video_url" value="{{ $lab->video_url }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload Thumbnail Baru</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500">
                    </div>
                </div>
            </div>
            <div class="flex gap-4 mt-8 pt-8 border-t">
                <a href="{{ route('virtual-labs.manage') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center rounded-xl font-bold">Batal</a>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-bold shadow-lg">Update</button>
            </div>
        </form>
    </div>
</div>
