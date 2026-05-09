@include('components/sidebar-beranda', ['headerSideNav' => 'Laporan Nilai Anak'])
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] p-6 md:p-10 bg-slate-50 min-h-screen">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-800 mb-6"><i class="fas fa-file-signature text-[#0071BC] mr-2"></i> Riwayat Nilai Tugas & Ujian</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold rounded-tl-xl">Tugas / Ujian</th>
                        <th class="p-4 font-bold">Tgl Tenggat</th>
                        <th class="p-4 font-bold">Status Kumpul</th>
                        <th class="p-4 font-bold text-right rounded-tr-xl">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($nilaiTugas as $nilai)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-semibold text-slate-700">{{ $nilai->judul }}</td>
                            <td class="p-4 text-sm text-slate-500">{{ \Carbon\Carbon::parse($nilai->end_date)->format('d M Y') }}</td>
                            <td class="p-4">
                                @if($nilai->tanggal_kumpul)
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Selesai</span>
                                @else
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Belum/Proses</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-black text-xl {{ ($nilai->nilai ?? 0) >= 75 ? 'text-[#0071BC]' : 'text-red-500' }}">
                                {{ $nilai->nilai ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-400 font-medium">Belum ada data nilai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>