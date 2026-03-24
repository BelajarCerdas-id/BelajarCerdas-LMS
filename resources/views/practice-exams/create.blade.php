<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Latihan Soal Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-gray-50">
    <div class="py-8 px-4 md:px-8">
        <div class="mb-8">
            <a href="{{ route('practice-exams.manage') }}" class="inline-flex items-center gap-2 text-yellow-600 hover:text-yellow-700 font-semibold mb-4">
                <i class="fa-solid fa-arrow-left"></i><span>Kembali</span>
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Latihan Soal</h1>
        </div>

        <form action="{{ route('practice-exams.store') }}" method="POST" class="bg-white rounded-3xl shadow-xl p-8">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipe Ujian *</label>
                        <select name="exam_type" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                            <option value="">Pilih Tipe</option>
                            <option value="daily_practice">Latihan Harian</option>
                            <option value="chapter_test">Ujian Bab</option>
                            <option value="midterm">UTS</option>
                            <option value="final">UAS</option>
                            <option value="school_exam">Ujian Sekolah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul *</label>
                        <input type="text" name="title" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi *</label>
                        <textarea name="description" rows="4" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500"></textarea>
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kurikulum *</label>
                        <select name="kurikulum_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                            <option value="">Pilih Kurikulum</option>
                            @foreach($kurikulums as $kur)
                                <option value="{{ $kur->id }}">{{ $kur->nama_kurikulum }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kelas *</label>
                        <select name="kelas_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mapel *</label>
                        <select name="mapel_id" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                            <option value="">Pilih Mapel</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Difficulty *</label>
                        <select name="difficulty" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Durasi (menit) *</label>
                            <input type="number" name="duration_minutes" value="60" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Total Soal *</label>
                            <input type="number" name="total_questions" value="30" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500">
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-4 mt-8 pt-8 border-t">
                <a href="{{ route('practice-exams.manage') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center rounded-xl font-bold">Batal</a>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl font-bold shadow-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>
