@include('components/sidebar-beranda', ['headerSideNav' => 'Data Warga Sekolah'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
    <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Yayasan</p>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Data Warga Sekolah</h1>
                <p class="text-slate-500 mt-2 font-medium">Lihat guru, seluruh siswa aktif, kelas aktif jika tersedia, dan data orang tua dari semua unit sekolah.</p>
            </div>
            <a href="{{ route('yayasan.dashboard', $yayasan->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        <form method="GET" action="{{ route('yayasan.people', $yayasan->id) }}" class="bg-white rounded-[1.75rem] p-5 md:p-6 shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Sekolah</label>
                <select name="school_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
                    <option value="">Semua sekolah</option>
                    @foreach ($sekolahList as $sekolah)
                        <option value="{{ $sekolah->id }}" @selected((string) $selectedSchoolId === (string) $sekolah->id)>{{ $sekolah->nama_sekolah }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Angkatan/Tahun</label>
                <select name="tahun_ajaran" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
                    <option value="">Semua tahun</option>
                    @foreach ($tahunAjaranList as $year)
                        <option value="{{ $year }}" @selected((string) $selectedYear === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Kelas</label>
                <select name="class_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>{{ $class->nama_sekolah }} - {{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="h-[46px] inline-flex items-center justify-center gap-2 px-6 rounded-2xl bg-[#0071BC] text-white font-black text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Guru</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $guru->count() }}</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Siswa Aktif</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $siswa->count() }}</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Kelas Dalam Filter</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $classes->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-black text-slate-900 mb-6">Daftar Guru</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Sekolah</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Nama</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">NIK</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Email</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="border border-slate-200 px-3 py-3 font-bold text-slate-700">{{ $item->nama_sekolah }}</td>
                                <td class="border border-slate-200 px-3 py-3 font-black text-slate-800">{{ $item->nama_lengkap }}</td>
                                <td class="border border-slate-200 px-3 py-3 text-slate-600">{{ $item->nik ?? '-' }}</td>
                                <td class="border border-slate-200 px-3 py-3 text-slate-600">{{ $item->personal_email ?? $item->email }}</td>
                                <td class="border border-slate-200 px-3 py-3"><span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#0071BC] text-[10px] font-black uppercase">{{ $item->status_akun }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="border border-slate-200 px-3 py-10 text-center text-slate-400 font-bold">Belum ada guru pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-black text-slate-900 mb-6">Daftar Siswa, Kelas, dan Orang Tua</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Sekolah</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Angkatan</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Kelas</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Siswa</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Akun Siswa</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Orang Tua</th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-black uppercase text-slate-400">Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswa as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="border border-slate-200 px-3 py-3 font-bold text-slate-700">{{ $item->nama_sekolah }}</td>
                                <td class="border border-slate-200 px-3 py-3 text-slate-600">{{ $item->tahun_ajaran ?? '-' }}</td>
                                <td class="border border-slate-200 px-3 py-3 font-black text-[#0071BC]">{{ $item->class_name ?? 'Belum masuk kelas' }}</td>
                                <td class="border border-slate-200 px-3 py-3 font-black text-slate-800">{{ $item->nama_siswa }}</td>
                                <td class="border border-slate-200 px-3 py-3 text-slate-600">{{ $item->personal_email ?? $item->akun_siswa }}</td>
                                <td class="border border-slate-200 px-3 py-3 font-bold text-slate-700">{{ $item->nama_orang_tua ?? '-' }}</td>
                                <td class="border border-slate-200 px-3 py-3 text-slate-600">{{ $item->pekerjaan_orang_tua ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="border border-slate-200 px-3 py-10 text-center text-slate-400 font-bold">Belum ada siswa pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
