@include('components/sidebar-beranda', ['headerSideNav' => 'Kehadiran Anak'])
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] p-6 md:p-10 bg-slate-50 min-h-screen">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-800 mb-6"><i class="fas fa-user-check text-emerald-500 mr-2"></i> Catatan Presensi Harian</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($absensi as $absen)
                <div class="p-5 border border-slate-100 rounded-2xl shadow-sm flex items-center justify-between hover:border-emerald-200 transition">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($absen->date)->translatedFormat('l') }}</p>
                        <p class="text-lg font-bold text-slate-700">{{ \Carbon\Carbon::parse($absen->date)->format('d M Y') }}</p>
                    </div>
                    <div class="px-4 py-2 rounded-xl text-sm font-bold text-white shadow-sm 
                        {{ strtolower($absen->status) == 'hadir' ? 'bg-emerald-500' : (strtolower($absen->status) == 'izin' || strtolower($absen->status) == 'sakit' ? 'bg-amber-400' : 'bg-red-500') }}">
                        {{ ucfirst($absen->status) }}
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-slate-400 font-medium">Belum ada data presensi.</div>
            @endforelse
        </div>
    </div>
</div>