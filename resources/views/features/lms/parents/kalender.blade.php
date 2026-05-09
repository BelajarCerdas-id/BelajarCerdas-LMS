@include('components/sidebar-beranda', ['headerSideNav' => 'Kalender Akademik'])
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] p-6 md:p-10 bg-slate-50 min-h-screen">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-800 mb-8"><i class="fas fa-calendar-alt text-[#0071BC] mr-2"></i> Agenda & Acara Sekolah</h2>
        <div class="relative border-l-4 border-slate-100 ml-4 space-y-8">
            @forelse($kalender as $agenda)
                <div class="relative pl-8">
                    <div class="absolute w-4 h-4 rounded-full bg-white border-4 border-[#0071BC] -left-[10px] top-1"></div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
                        <span class="inline-block px-3 py-1 bg-[#0071BC]/10 text-[#0071BC] font-bold text-xs rounded-lg mb-2">
                            {{ \Carbon\Carbon::parse($agenda->date)->format('d M Y') }}
                        </span>
                        <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $agenda->title }}</h3>
                        @if($agenda->description)
                            <p class="text-sm text-slate-500">{{ $agenda->description }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="pl-8 text-slate-400 font-medium">Tidak ada agenda akademik dalam waktu dekat.</div>
            @endforelse
        </div>
    </div>
</div>