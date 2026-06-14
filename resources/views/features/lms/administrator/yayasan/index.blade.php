@include('components/sidebar-beranda', ['headerSideNav' => 'Manajemen Yayasan'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div>
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400">
                            <li>Administrator</li>
                            <li><i class="fas fa-chevron-right text-[8px]"></i></li>
                            <li class="text-amber-600">Manajemen Yayasan</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Manajemen Yayasan</h1>
                    <p class="text-slate-500 mt-2 font-medium">Kelola data yayasan, akun pengurus, dan sekolah yang dinaungi.</p>
                </div>

                <a href="{{ route('admin.yayasan.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-amber-500 text-white rounded-xl font-black text-sm shadow-lg shadow-amber-100 hover:bg-amber-600 transition-all">
                    <i class="fas fa-circle-plus"></i> Tambah Yayasan
                </a>
            </div>

            @if (session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-sm font-bold text-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                @if ($yayasans->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-slate-300">
                        <i class="fas fa-building-columns text-5xl mb-4"></i>
                        <p class="text-sm font-bold">Belum ada yayasan terdaftar</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="border border-slate-200 px-3 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Yayasan</th>
                                    <th class="border border-slate-200 px-3 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Akun Pengurus</th>
                                    <th class="border border-slate-200 px-3 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Unit Sekolah</th>
                                    <th class="border border-slate-200 px-3 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Kontak</th>
                                    <th class="border border-slate-200 px-3 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($yayasans as $yayasan)
                                    @php
                                        $profile = $yayasan->YayasanProfiles->first();
                                        $userAccount = optional($profile)->UserAccount;
                                    @endphp
                                    <tr class="hover:bg-amber-50/40 transition-colors">
                                        <td class="border border-slate-200 px-3 py-4">
                                            <div class="flex items-center gap-3">
                                                @if ($yayasan->logo)
                                                    <img src="{{ asset($yayasan->logo) }}" class="w-12 h-12 rounded-xl object-contain bg-slate-50 border border-slate-100" alt="Logo Yayasan">
                                                @else
                                                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                                        <i class="fas fa-building-columns"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="font-black text-slate-900">{{ $yayasan->nama_yayasan }}</p>
                                                    <p class="text-xs text-slate-400 font-bold">NPWP: {{ $yayasan->npwp ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-4">
                                            <p class="font-bold text-slate-800">{{ optional($profile)->nama_lengkap ?? '-' }}</p>
                                            <p class="text-xs text-slate-500">{{ optional($userAccount)->email ?? '-' }}</p>
                                            <span class="inline-flex mt-2 px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ optional($userAccount)->status_akun === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                                {{ optional($userAccount)->status_akun ?? 'tanpa akun' }}
                                            </span>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-4">
                                            <p class="font-black text-amber-600 text-lg">{{ $yayasan->school_partners_count }}</p>
                                            <p class="text-xs text-slate-400 font-bold">sekolah dinaungi</p>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-4 text-slate-600">
                                            <p>{{ $yayasan->email ?? '-' }}</p>
                                            <p class="text-xs text-slate-400">{{ $yayasan->kontak ?? '-' }}</p>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-4">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('admin.yayasan.edit', $yayasan->id) }}" class="text-amber-600 font-black text-xs uppercase hover:text-amber-700">
                                                    <i class="fas fa-pen mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.yayasan.destroy', $yayasan->id) }}" method="POST" onsubmit="return confirm('Hapus yayasan ini? Sekolah terkait akan dilepas dari yayasan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-500 font-black text-xs uppercase hover:text-red-700 cursor-pointer" type="submit">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif
