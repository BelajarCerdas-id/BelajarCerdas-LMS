<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'TKA Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen bg-gray-50">
    <div class="py-8 px-4 md:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('tka-exams.manage') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 font-semibold mb-4">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah TKA Baru</h1>
            <p class="text-gray-600">Buat Simulasi Tes Kompetensi Akademik</p>
        </div>

        <!-- Form -->
        <form action="{{ route('tka-exams.store') }}" method="POST" class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul TKA *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi *</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subjects -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mata Pelajaran *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @php
                                $subjects = ['Matematika', 'IPA', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Ekonomi', 'Geografi', 'Sejarah'];
                            @endphp
                            @foreach($subjects as $subj)
                                <label class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500 transition">
                                    <input type="checkbox" name="subjects[]" value="{{ $subj }}" 
                                        {{ in_array($subj, old('subjects', [])) ? 'checked' : '' }}
                                        class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                                    <span class="text-sm font-medium text-gray-700">{{ $subj }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('subjects')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Difficulty -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tingkat Kesulitan *</label>
                        <select name="difficulty" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('difficulty') border-red-500 @enderror">
                            <option value="">Pilih Difficulty</option>
                            <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy (Mudah)</option>
                            <option value="medium" {{ old('difficulty') == 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                            <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard (Sulit)</option>
                            <option value="mixed" {{ old('difficulty') == 'mixed' ? 'selected' : '' }}>Mixed (Campuran)</option>
                        </select>
                        @error('difficulty')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Durasi (menit) *</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 90) }}" required min="30" max="180"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('duration_minutes') border-red-500 @enderror">
                        @error('duration_minutes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Passing Score -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Passing Score (%) *</label>
                        <input type="number" name="passing_score" value="{{ old('passing_score', 70) }}" required min="50" max="100"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('passing_score') border-red-500 @enderror">
                        @error('passing_score')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Total Questions -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Total Soal *</label>
                        <input type="number" name="total_questions" value="{{ old('total_questions', 30) }}" required min="10" max="100"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('total_questions') border-red-500 @enderror">
                        @error('total_questions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Randomize Questions -->
                    <div>
                        <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500 transition">
                            <input type="checkbox" name="randomize_questions" value="1" {{ old('randomize_questions') ? 'checked' : '' }}
                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                            <div>
                                <p class="font-bold text-gray-700">Acak Soal</p>
                                <p class="text-sm text-gray-500">Urutan soal akan diacak untuk setiap peserta</p>
                            </div>
                        </label>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai *</label>
                            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Berakhir *</label>
                            <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+1 year'))) }}" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 transition @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 mt-8 pt-8 border-t">
                <a href="{{ route('tka-exams.manage') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center rounded-xl hover:bg-gray-300 transition font-bold">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition font-bold shadow-lg">
                    <i class="fa-solid fa-save mr-2"></i>Simpan TKA
                </button>
            </div>
        </form>
    </div>
</div>
