@include('components/sidebar-beranda', [
    'headerSideNav' => $yayasan ? 'Edit Yayasan' : 'Tambah Yayasan',
    'linkBackButton' => route('admin.yayasan.index'),
    'backButton' => '<i class="fas fa-arrow-left"></i>',
])

@if (Auth::user()->role === 'Administrator')
    @php
        $isEdit = filled($yayasan);
        $action = $isEdit ? route('admin.yayasan.update', $yayasan->id) : route('admin.yayasan.store');
        $oldSchoolIds = old('school_ids', $selectedSchoolIds ?? []);
    @endphp

    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400">
                        <li>Administrator</li>
                        <li><i class="fas fa-chevron-right text-[8px]"></i></li>
                        <li><a href="{{ route('admin.yayasan.index') }}" class="hover:text-slate-600">Manajemen Yayasan</a></li>
                        <li><i class="fas fa-chevron-right text-[8px]"></i></li>
                        <li class="text-amber-600">{{ $isEdit ? 'Edit' : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $isEdit ? 'Edit Yayasan' : 'Tambah Yayasan' }}</h1>
                <p class="text-slate-500 mt-2 font-medium">Lengkapi profil yayasan, akun pengurus, dan unit sekolah binaan.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
                    <p class="font-black text-sm mb-2">Periksa kembali input berikut:</p>
                    <ul class="list-disc pl-5 text-sm font-bold space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" autocomplete="OFF" class="space-y-6">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-building-columns"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Profil Yayasan</h2>
                            <p class="text-xs text-slate-400 font-bold">Data lembaga penaung sekolah</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Yayasan <sup class="text-red-500">*</sup></label>
                            <input type="text" name="nama_yayasan" value="{{ old('nama_yayasan', optional($yayasan)->nama_yayasan) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', optional($yayasan)->npwp) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email Yayasan</label>
                            <input type="email" name="email_yayasan" value="{{ old('email_yayasan', optional($yayasan)->email) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kontak</label>
                            <input type="text" name="kontak" value="{{ old('kontak', optional($yayasan)->kontak) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Alamat</label>
                            <textarea name="alamat" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">{{ old('alamat', optional($yayasan)->alamat) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Logo Yayasan</label>
                            <input type="file" name="logo" accept="image/png,image/jpeg" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm">
                            @if ($isEdit && $yayasan->logo)
                                <img src="{{ asset($yayasan->logo) }}" class="mt-3 w-28 h-20 rounded-xl object-contain bg-slate-50 border border-slate-100" alt="Logo Yayasan">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Akun Pengurus Yayasan</h2>
                            <p class="text-xs text-slate-400 font-bold">Akun login role Yayasan</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Pengurus <sup class="text-red-500">*</sup></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', optional($profile)->nama_lengkap) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">No HP <sup class="text-red-500">*</sup></label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', optional($userAccount)->no_hp) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email Login <sup class="text-red-500">*</sup></label>
                            <input type="email" name="email" value="{{ old('email', optional($userAccount)->email) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }} <sup class="text-red-500">{{ $isEdit ? '' : '*' }}</sup></label>
                            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status Akun <sup class="text-red-500">*</sup></label>
                            <select name="status_akun" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none bg-white text-sm">
                                @foreach (['aktif' => 'Aktif', 'non-aktif' => 'Non-Aktif'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('status_akun', optional($userAccount)->status_akun ?? 'aktif') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-school-flag"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Unit Sekolah Binaan</h2>
                            <p class="text-xs text-slate-400 font-bold">Centang sekolah yang berada di bawah yayasan ini</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-2">
                        @forelse ($schools as $school)
                            @php
                                $checked = in_array($school->id, $oldSchoolIds) || ($isEdit && (int) $school->yayasan_id === (int) $yayasan->id);
                                $usedByOther = $school->yayasan_id && (!$isEdit || (int) $school->yayasan_id !== (int) $yayasan->id);
                            @endphp
                            <label class="flex items-start gap-3 rounded-2xl border {{ $checked ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} p-4 cursor-pointer hover:border-amber-300 transition">
                                <input type="checkbox" name="school_ids[]" value="{{ $school->id }}" class="mt-1 checkbox checkbox-warning" {{ $checked ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-black text-slate-800">{{ $school->nama_sekolah }}</span>
                                    <span class="block text-xs text-slate-400 font-bold">{{ $school->jenjang_sekolah }} | NPSN {{ $school->npsn }}</span>
                                    @if ($usedByOther)
                                        <span class="inline-flex mt-2 text-[10px] font-black text-orange-600 bg-orange-50 px-2 py-1 rounded-lg">
                                            Saat ini dinaungi {{ optional($school->Yayasan)->nama_yayasan }}. Akan dipindahkan jika dicentang.
                                        </span>
                                    @elseif ($checked)
                                        <span class="inline-flex mt-2 text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                                            Sudah dinaungi yayasan ini.
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <div class="md:col-span-2 text-center py-12 text-slate-300 font-bold">
                                Belum ada sekolah partner.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8 border-t border-slate-100 pt-8">
                        <div class="mb-5">
                            <h3 class="text-lg font-black text-slate-900">Input Sekolah Baru</h3>
                            <p class="text-xs text-slate-400 font-bold">Opsional. Isi baris ini jika sekolah belum ada di daftar pilihan di atas.</p>
                        </div>

                        @if ($headmasterAccounts->isEmpty())
                            <div class="rounded-2xl bg-orange-50 text-orange-700 p-4 text-sm font-bold">
                                Belum ada akun Kepala Sekolah/Guru yang bisa dipilih sebagai `kepsek_id`. Buat akun sekolah terlebih dahulu sebelum input sekolah baru dari form ini.
                            </div>
                        @else
                            <div class="space-y-4">
                                @for ($i = 0; $i < 3; $i++)
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                                        <div>
                                            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">Nama Sekolah</label>
                                            <input type="text" name="new_schools[{{ $i }}][nama_sekolah]" value="{{ old("new_schools.$i.nama_sekolah") }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">NPSN</label>
                                            <input type="text" name="new_schools[{{ $i }}][npsn]" value="{{ old("new_schools.$i.npsn") }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">Jenjang</label>
                                            <select name="new_schools[{{ $i }}][jenjang_sekolah]" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm bg-white">
                                                <option value="">Pilih</option>
                                                @foreach (['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'MAK'] as $jenjang)
                                                    <option value="{{ $jenjang }}" {{ old("new_schools.$i.jenjang_sekolah") === $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">Kepala Sekolah</label>
                                            <select name="new_schools[{{ $i }}][kepsek_id]" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 outline-none text-sm bg-white">
                                                <option value="">Pilih akun</option>
                                                @foreach ($headmasterAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ (string) old("new_schools.$i.kepsek_id") === (string) $account->id ? 'selected' : '' }}>
                                                        {{ $account->email }} - {{ $account->role }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('admin.yayasan.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-black text-sm text-center hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-8 py-3 bg-amber-500 text-white rounded-xl font-black text-sm shadow-lg shadow-amber-100 hover:bg-amber-600 transition cursor-pointer">
                        <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Yayasan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif
