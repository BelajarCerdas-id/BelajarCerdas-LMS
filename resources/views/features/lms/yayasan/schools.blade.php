@include('components/sidebar-beranda', ['headerSideNav' => 'Kelola Sekolah'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
    <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">

        <div class="relative overflow-hidden rounded-[2rem] bg-[#0071BC] p-7 md:p-10 text-white shadow-xl shadow-slate-200">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#0071BC]/35 blur-2xl"></div>
            <div class="absolute right-24 bottom-0 h-40 w-40 rounded-full bg-amber-300/20 blur-xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div>
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white/60">
                            <li>Yayasan</li>
                            <li><i class="fas fa-chevron-right text-[8px]"></i></li>
                            <li class="text-white">Kelola Sekolah</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight">{{ $yayasan->nama_yayasan }}</h1>
                    <p class="text-white/75 mt-3 max-w-2xl font-medium">
                        Pusat kontrol unit sekolah binaan, status kepala sekolah, dan akses cepat ke data operasional tiap sekolah.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('yayasan.people', $yayasan->id) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white/10 border border-white/15 text-white rounded-2xl font-black text-sm hover:bg-white/20 transition-all">
                        <i class="fas fa-users-viewfinder"></i> Data Warga
                    </a>
                    <a href="{{ route('yayasan.dashboard', $yayasan->id) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white text-slate-900 rounded-2xl font-black text-sm shadow-lg hover:bg-slate-100 transition-all">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-sm flex items-center justify-between">
                <p class="font-bold text-sm"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
            </div>
        @endif

        @php
            $totalSekolah = $sekolahList->count();
            $sekolahDenganKepsek = $sekolahList->filter(fn ($sekolah) => ! empty($sekolah->kepsek_id))->count();
            $jenjangList = $sekolahList->pluck('jenjang_sekolah')->filter()->unique()->values();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white rounded-[1.5rem] p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-school"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Sekolah</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $totalSekolah }}</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-user-tie"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Kepsek Terhubung</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $sekolahDenganKepsek }}</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-layer-group"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Jenjang</p>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ $jenjangList->isNotEmpty() ? $jenjangList->join(', ') : '-' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Unit Binaan</p>
                    <h2 class="text-2xl font-black text-slate-900">Daftar Sekolah</h2>
                </div>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-500 text-xs font-black uppercase tracking-wider w-max">
                    <i class="fas fa-database"></i> {{ $totalSekolah }} data
                </span>
            </div>

            @if ($sekolahList->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50 text-slate-400">
                    <i class="fas fa-school text-5xl mb-4"></i>
                    <p class="text-sm font-bold">Belum ada sekolah terdaftar</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach ($sekolahList as $sekolah)
                        <div class="group relative overflow-hidden rounded-[1.75rem] border border-slate-100 bg-slate-50 p-5 hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all">
                            <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-blue-100/70 group-hover:bg-blue-200/70 transition-all"></div>
                            <div class="relative flex items-start gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                                    @if (! empty($sekolah->logo))
                                        <img src="{{ asset($sekolah->logo) }}" alt="Logo {{ $sekolah->nama_sekolah }}" class="w-full h-full object-contain p-2">
                                    @else
                                        <i class="fas fa-school text-2xl text-[#0071BC]"></i>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#0071BC] text-[10px] font-black uppercase tracking-wider">{{ $sekolah->jenjang_sekolah }}</span>
                                        @if ($sekolah->kepsek_id)
                                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider">Kepsek aktif</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-wider">Belum ada kepsek</span>
                                        @endif
                                    </div>

                                    <h3 class="font-black text-lg text-slate-900 leading-tight truncate">{{ $sekolah->nama_sekolah }}</h3>
                                    <p class="text-xs font-bold text-slate-400 mt-1">NPSN {{ $sekolah->npsn ?? '-' }}</p>
                                    <p class="text-xs font-bold text-slate-500 mt-3">
                                        <i class="fas fa-envelope text-slate-300 mr-1"></i>
                                        {{ $sekolah->UserAccount->email ?? 'Email kepala sekolah belum tersedia' }}
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <a href="{{ route('yayasan.school.edit', [$yayasan->id, $sekolah->id]) }}"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-[#0071BC] text-white text-xs font-black hover:bg-blue-700 transition-all">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="{{ route('yayasan.people', ['yayasanId' => $yayasan->id, 'school_id' => $sekolah->id]) }}"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-black hover:border-[#0071BC] hover:text-[#0071BC] transition-all">
                                    <i class="fas fa-users"></i> Warga
                                </a>
                                <a href="{{ route('yayasan.calendar', ['yayasanId' => $yayasan->id, 'school_id' => $sekolah->id]) }}"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-black hover:border-[#0071BC] hover:text-[#0071BC] transition-all">
                                    <i class="fas fa-calendar-days"></i> Agenda
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
