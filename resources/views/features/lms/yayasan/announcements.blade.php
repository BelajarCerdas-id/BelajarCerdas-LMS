@include('components/sidebar-beranda', ['headerSideNav' => 'Pengumuman Kepala Sekolah'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
    <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Yayasan</p>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Pengumuman Kepala Sekolah</h1>
                <p class="text-slate-500 mt-2 font-medium">Kirim informasi dari yayasan hanya ke kepala sekolah tiap unit.</p>
            </div>
            <a href="{{ route('yayasan.dashboard', $yayasan->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-sm font-bold text-sm">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm font-bold text-sm">
                <i class="fas fa-triangle-exclamation mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100 h-max">
                <h2 class="text-xl font-black text-slate-900 mb-5">Buat Pengumuman</h2>
                <form method="POST" action="{{ route('yayasan.announcements.store', $yayasan->id) }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Tujuan Sekolah</label>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 max-h-56 overflow-y-auto custom-scrollbar space-y-2">
                            @forelse ($sekolahList as $sekolah)
                                <label class="flex items-start gap-3 rounded-xl bg-white border border-slate-100 p-3 cursor-pointer hover:border-[#0071BC] transition-all">
                                    <input type="checkbox" name="school_ids[]" value="{{ $sekolah->id }}" class="mt-1 checkbox checkbox-sm" {{ $sekolah->kepsek_id ? '' : 'disabled' }}>
                                    <span>
                                        <span class="block text-sm font-black text-slate-800">{{ $sekolah->nama_sekolah }}</span>
                                        <span class="block text-xs font-bold {{ $sekolah->kepsek_id ? 'text-slate-400' : 'text-red-400' }}">
                                            {{ $sekolah->kepsek_id ? 'Kepala sekolah tersedia' : 'Belum ada kepala sekolah' }}
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm font-bold text-slate-400 text-center py-6">Belum ada sekolah.</p>
                            @endforelse
                        </div>
                        @error('school_ids') <p class="text-xs font-bold text-red-500 mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Judul</label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]" placeholder="Contoh: Koordinasi Bulanan Yayasan">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Jenis</label>
                        <select name="type" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
                            <option value="info">Info</option>
                            <option value="penting">Penting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Isi</label>
                        <textarea name="content" required rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]" placeholder="Tuliskan isi pengumuman...">{{ old('content') }}</textarea>
                    </div>
                    <button class="w-full py-3 rounded-2xl bg-[#0071BC] text-white font-black text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim ke Kepala Sekolah
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Terkirim</p>
                        <p class="text-4xl font-black text-slate-900 mt-2">{{ $totalSent }}</p>
                    </div>
                    <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Terbaca</p>
                        <p class="text-4xl font-black text-emerald-600 mt-2">{{ $totalRead }}</p>
                    </div>
                    <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Belum Dibaca</p>
                        <p class="text-4xl font-black text-amber-600 mt-2">{{ max(0, $totalSent - $totalRead) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-black text-slate-900 mb-6">Riwayat Pengiriman</h2>
                    <div class="space-y-4">
                        @forelse ($announcements as $item)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#0071BC] text-[10px] font-black uppercase">{{ $item->nama_sekolah }}</span>
                                            <span class="px-2.5 py-1 rounded-lg {{ $item->type === 'penting' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500' }} text-[10px] font-black uppercase">{{ $item->type }}</span>
                                        </div>
                                        <h3 class="font-black text-slate-900">{{ $item->title }}</h3>
                                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ $item->content }}</p>
                                        <p class="text-xs font-bold text-slate-400 mt-3">Dikirim {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }}</p>
                                    </div>
                                    @if ($item->read_at)
                                        <span class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-black">
                                            <i class="fas fa-check-double"></i> Terbaca {{ \Carbon\Carbon::parse($item->read_at)->translatedFormat('d M H:i') }}
                                        </span>
                                    @else
                                        <span class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 text-amber-600 text-xs font-black">
                                            <i class="fas fa-clock"></i> Terkirim
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-16 text-slate-400">
                                <i class="fas fa-bullhorn text-4xl mb-3"></i>
                                <p class="text-sm font-bold">Belum ada pengumuman dari yayasan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
