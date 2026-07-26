@include('components/sidebar-beranda', ['headerSideNav' => 'Laporan Nilai Anak'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] p-6 md:p-10 bg-slate-50 min-h-screen">

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fas fa-file-signature text-[#0071BC] mr-2"></i>
                    Riwayat Nilai Anak
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Seluruh nilai tugas, ujian, proyek, remedial, maupun pengayaan.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">

                        <th class="p-4 text-left rounded-tl-xl">
                            Asesmen
                        </th>

                        <th class="p-4 text-left">
                            Mapel
                        </th>

                        <th class="p-4 text-left">
                            Kelas
                        </th>

                        <th class="p-4 text-left">
                            Tipe
                        </th>

                        <th class="p-4 text-left">
                            Deadline
                        </th>

                        <th class="p-4 text-center">
                            Status
                        </th>

                        <th class="p-4 text-center">
                            Sumber Nilai
                        </th>

                        <th class="p-4 text-right rounded-tr-xl">
                            Nilai
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($nilaiTugas as $nilai)
                        @php
                            $scoreSource = match($nilai->score_source) {
                                'main'       => 'Nilai Utama',
                                'susulan'    => 'Susulan',
                                'remedial'   => 'Remedial',
                                'pengayaan'  => 'Pengayaan',
                                default      => '-',
                            };

                            $badgeColor = match($nilai->score_source) {
                                'main'       => 'bg-blue-100 text-blue-700',
                                'susulan'    => 'bg-amber-100 text-amber-700',
                                'remedial'   => 'bg-red-100 text-red-700',
                                'pengayaan'  => 'bg-emerald-100 text-emerald-700',
                                default      => 'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <tr class="hover:bg-slate-50 transition">

                            <td class="p-4">

                                <div class="font-semibold text-slate-800">
                                    {{ $nilai->judul }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $scoreSource }}
                                </div>

                            </td>

                            <td class="p-4">

                                <span class="font-medium text-slate-700">
                                    {{ $nilai->mapel }}
                                </span>

                            </td>

                            <td class="p-4">

                                <span class="text-slate-600">
                                    {{ $nilai->kelas }}
                                </span>

                            </td>

                            <td class="p-4">

                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                    {{ $nilai->tipe }}
                                </span>

                            </td>

                            <td class="p-4 text-sm text-slate-600">

                                @if($nilai->deadline)
                                    {{ \Carbon\Carbon::parse($nilai->deadline)->format('d M Y H:i') }}
                                @else
                                    -
                                @endif

                            </td>

                            <td class="p-4 text-center">
                                @if($nilai->status == 'Selesai')
                                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                        Belum Dinilai
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeColor }}">
                                    {{ $scoreSource }}
                                </span>
                            </td>

                            <td class="p-4 text-right">
                                @if($nilai->nilai !== null)

                                    <span class="text-2xl font-black {{ $nilai->nilai >= 75 ? 'text-[#0071BC]' : 'text-red-500' }}">
                                        {{ $nilai->nilai }}
                                    </span>

                                @else
                                    <span class="text-slate-400 font-bold">
                                        -
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 font-medium">
                                Belum ada data nilai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>