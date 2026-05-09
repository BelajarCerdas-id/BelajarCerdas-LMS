@include('components/sidebar-beranda', ['headerSideNav' => 'Jadwal Pelajaran'])
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] p-6 md:p-10 bg-slate-50 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($jadwalPerHari as $hari => $jadwal)
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-xl font-bold text-[#0071BC] mb-4 border-b border-slate-100 pb-3"><i class="far fa-calendar text-slate-400 mr-2"></i> Hari {{ $hari }}</h3>
                <div class="space-y-3">
                    @foreach($jadwal as $item)
                        <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="w-16 text-center shrink-0">
                                <span class="block text-xs font-black text-slate-700">{{ substr($item->start_time, 0, 5) }}</span>
                                <span class="block text-[10px] font-bold text-slate-400">{{ substr($item->end_time, 0, 5) }}</span>
                            </div>
                            <div class="w-1 h-8 bg-[#0071BC] rounded-full"></div>
                            <div>
                                <h4 class="font-bold text-slate-800">{{ $item->subject_name }}</h4>
                                <p class="text-xs font-medium text-slate-500"><i class="fas fa-user-tie mr-1"></i> {{ $item->guru ?? 'Guru Mapel' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-10 text-center shadow-sm border border-slate-200 text-slate-400 font-medium">Jadwal pelajaran belum tersedia.</div>
        @endforelse
    </div>
</div>