@include('components/sidebar-beranda', ['headerSideNav' => 'Edit Sekolah'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F1F5F9] min-h-screen pb-12">

    <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <li class="inline-flex items-center">Yayasan</li>
                        <li><i class="fas fa-chevron-right mx-2 text-[8px]"></i></li>
                        <li><a href="{{ route('yayasan.schools', $yayasan->id) }}" class="hover:text-slate-600 transition-colors">Kelola Sekolah</a></li>
                        <li><i class="fas fa-chevron-right mx-2 text-[8px]"></i></li>
                        <li class="text-[#0071BC]">Edit</li>
                    </ol>
                </nav>

                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $sekolah->nama_sekolah }}
                </h1>
                <p class="text-slate-500 mt-2 font-medium">Perbarui data sekolah</p>
            </div>

            <a href="{{ route('yayasan.schools', $yayasan->id) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100 max-w-2xl">
            <form action="{{ route('yayasan.school.update', [$yayasan->id, $sekolah->id]) }}" method="POST" autocomplete="OFF">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Sekolah <sup class="text-red-500">*</sup></label>
                        <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none text-sm"
                            placeholder="Masukkan nama sekolah">
                        @error('nama_sekolah')
                            <span class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">NPSN <sup class="text-red-500">*</sup></label>
                        <input type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none text-sm"
                            placeholder="Masukkan NPSN">
                        @error('npsn')
                            <span class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenjang <sup class="text-red-500">*</sup></label>
                        <select name="jenjang_sekolah"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none bg-white text-sm text-slate-700">
                            @foreach (['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'MAK'] as $jenjang)
                                <option value="{{ $jenjang }}" {{ $sekolah->jenjang_sekolah === $jenjang ? 'selected' : '' }}>
                                    {{ $jenjang }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenjang_sekolah')
                            <span class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-8">
                    <button type="submit"
                        class="px-8 py-3 bg-[#0071BC] text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
