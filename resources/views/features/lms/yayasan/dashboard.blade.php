@include('components/sidebar-beranda', ['headerSideNav' => 'Pusat Kendali Yayasan'])

@php
    $kepsekCoverage = $totalSekolah > 0 ? round(($sekolahDenganKepsek / $totalSekolah) * 100) : 0;
    $readRate = $totalPengumuman > 0 ? round(($pengumumanDibaca / $totalPengumuman) * 100) : 0;
    $topSchool = $sekolah->first();
@endphp

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
    <div class="p-4 sm:p-6 md:p-10 max-w-7xl mx-auto space-y-6 md:space-y-8">

        <div class="relative overflow-hidden rounded-[1.5rem] md:rounded-[2rem] bg-[#0071BC] p-5 sm:p-7 md:p-10 text-white shadow-xl shadow-blue-100">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/15 blur-2xl"></div>
            <div class="absolute right-28 bottom-0 h-44 w-44 rounded-full bg-cyan-200/20 blur-xl"></div>
            <div class="absolute left-8 bottom-8 h-20 w-20 rounded-full border border-white/10"></div>

            <div class="relative grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-6 md:gap-8 items-end">
                <div class="min-w-0">
                    <div class="inline-flex max-w-full items-center gap-2 rounded-full bg-white/15 border border-white/20 px-3 sm:px-4 py-2 text-[10px] sm:text-xs font-black uppercase tracking-[0.16em] sm:tracking-[0.2em] mb-5">
                        <i class="fas fa-building-columns"></i>
                        <span class="truncate">Dashboard Global</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-black tracking-tight leading-tight break-words">
                        {{ $yayasan->nama_yayasan }}
                    </h1>
                    <p class="text-sm sm:text-base text-white/85 mt-3 max-w-2xl font-medium leading-relaxed">
                        Ringkasan real-time dari unit sekolah binaan, warga sekolah aktif, agenda, pengumuman, dan transaksi berhasil.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-7 max-w-3xl">
                        <div class="rounded-2xl bg-white/12 border border-white/15 p-3 sm:p-4 min-w-0">
                            <p class="text-xl sm:text-2xl font-black truncate">{{ $totalSekolah }}</p>
                            <p class="text-[11px] text-white/70 font-bold uppercase mt-1">Sekolah</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 border border-white/15 p-3 sm:p-4 min-w-0">
                            <p class="text-xl sm:text-2xl font-black truncate">{{ number_format($totalSiswa, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-white/70 font-bold uppercase mt-1">Siswa Aktif</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 border border-white/15 p-3 sm:p-4 min-w-0">
                            <p class="text-xl sm:text-2xl font-black truncate">{{ number_format($totalTenagaPendidik, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-white/70 font-bold uppercase mt-1">Staff Aktif</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 border border-white/15 p-3 sm:p-4 min-w-0">
                            <p class="text-xl sm:text-2xl font-black truncate">{{ $agendaBulanIni }}</p>
                            <p class="text-[11px] text-white/70 font-bold uppercase mt-1">Agenda Bulan Ini</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] md:rounded-[1.75rem] bg-white text-slate-900 p-4 sm:p-5 shadow-lg min-w-0">
                    <p class="text-[11px] sm:text-xs font-black uppercase tracking-[0.18em] text-[#0071BC] mb-4">Akses Cepat</p>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('yayasan.schools', $yayasan->id) }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:p-4 hover:border-[#0071BC] hover:bg-blue-50 transition-all min-w-0">
                            <i class="fas fa-school-flag text-[#0071BC] text-lg"></i>
                            <p class="text-xs sm:text-sm font-black mt-3 leading-snug">Kelola Sekolah</p>
                        </a>
                        <a href="{{ route('yayasan.people', $yayasan->id) }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:p-4 hover:border-[#0071BC] hover:bg-blue-50 transition-all min-w-0">
                            <i class="fas fa-users-viewfinder text-[#0071BC] text-lg"></i>
                            <p class="text-xs sm:text-sm font-black mt-3 leading-snug">Data Warga</p>
                        </a>
                        <a href="{{ route('yayasan.calendar', $yayasan->id) }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:p-4 hover:border-[#0071BC] hover:bg-blue-50 transition-all min-w-0">
                            <i class="fas fa-calendar-days text-[#0071BC] text-lg"></i>
                            <p class="text-xs sm:text-sm font-black mt-3 leading-snug">Kalender</p>
                        </a>
                        <a href="{{ route('yayasan.announcements', $yayasan->id) }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:p-4 hover:border-[#0071BC] hover:bg-blue-50 transition-all min-w-0">
                            <i class="fas fa-bullhorn text-[#0071BC] text-lg"></i>
                            <p class="text-xs sm:text-sm font-black mt-3 leading-snug break-words">Pengumuman</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="bg-white rounded-[1.75rem] p-5 sm:p-6 border border-slate-100 shadow-sm min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-slate-400 text-[11px] sm:text-xs font-black uppercase tracking-widest">Pendapatan Berhasil</p>
                        <p class="text-xl sm:text-2xl xl:text-[1.65rem] font-black text-slate-900 mt-3 leading-tight break-words">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-slate-400 mt-4">Dihitung dari transaksi berstatus berhasil.</p>
            </div>

            <div class="bg-white rounded-[1.75rem] p-5 sm:p-6 border border-slate-100 shadow-sm min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-slate-400 text-[11px] sm:text-xs font-black uppercase tracking-widest">Kepsek Terhubung</p>
                        <p class="text-3xl sm:text-4xl font-black text-slate-900 mt-3">{{ $sekolahDenganKepsek }}</p>
                    </div>
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $kepsekCoverage }}%"></div>
                </div>
                <p class="text-xs font-bold text-slate-400 mt-2">{{ $kepsekCoverage }}% sekolah punya kepala sekolah.</p>
            </div>

            <div class="bg-white rounded-[1.75rem] p-5 sm:p-6 border border-slate-100 shadow-sm min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-slate-400 text-[11px] sm:text-xs font-black uppercase tracking-widest">Belum Ada Kepsek</p>
                        <p class="text-3xl sm:text-4xl font-black text-slate-900 mt-3">{{ $sekolahTanpaKepsek }}</p>
                    </div>
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-slate-400 mt-4">Perlu ditindaklanjuti di Kelola Sekolah.</p>
            </div>

            <div class="bg-white rounded-[1.75rem] p-5 sm:p-6 border border-slate-100 shadow-sm min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-slate-400 text-[11px] sm:text-xs font-black uppercase tracking-widest">Pengumuman Dibaca</p>
                        <p class="text-3xl sm:text-4xl font-black text-slate-900 mt-3">{{ $readRate }}%</p>
                    </div>
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-slate-400 mt-4">{{ $pengumumanDibaca }} dari {{ $totalPengumuman }} pengumuman sudah dibaca.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-[1.5rem] md:rounded-[2rem] p-5 sm:p-6 md:p-8 shadow-sm border border-slate-100 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Distribusi Siswa</p>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">Komparasi Unit Sekolah</h3>
                    </div>
                    <span class="inline-flex w-max max-w-full items-center gap-2 rounded-full bg-blue-50 px-3 sm:px-4 py-2 text-[11px] sm:text-xs font-black text-[#0071BC]">
                        <i class="fas fa-database"></i> Data real database
                    </span>
                </div>

                @if ($sekolah->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 rounded-[1.5rem] bg-slate-50 text-slate-400">
                        <i class="fas fa-school text-5xl mb-4"></i>
                        <p class="text-sm font-bold">Belum ada sekolah terdaftar</p>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach ($sekolah as $unit)
                            @php
                                $percentage = $totalSiswa > 0 ? min(100, round(($unit->total_siswa / $totalSiswa) * 100)) : 0;
                            @endphp
                            <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 sm:p-5 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <div class="min-w-0">
                                        <p class="font-black text-sm sm:text-base text-slate-900 break-words">{{ $unit->nama_sekolah }}</p>
                                        <p class="text-[11px] sm:text-xs font-bold text-slate-400 mt-1 break-words">{{ $unit->jenjang_sekolah ?? '-' }} | NPSN {{ $unit->npsn ?? '-' }}</p>
                                    </div>
                                    <div class="text-left sm:text-right shrink-0">
                                        <p class="text-sm font-black text-[#0071BC]">{{ number_format($unit->total_siswa, 0, ',', '.') }} siswa</p>
                                        <p class="text-[11px] font-bold text-slate-400">{{ $percentage }}% dari total</p>
                                    </div>
                                </div>
                                <div class="h-3 bg-white rounded-full overflow-hidden border border-slate-100">
                                    <div class="h-full bg-gradient-to-r from-[#0071BC] to-cyan-400 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-slate-950 rounded-[1.5rem] md:rounded-[2rem] p-5 sm:p-6 md:p-8 text-white shadow-xl shadow-slate-200 min-w-0">
                    <p class="text-cyan-300 text-xs font-black uppercase tracking-[0.25em] mb-2">Sorotan</p>
                    <h3 class="text-xl font-black mb-6">Unit Terbesar</h3>

                    @if ($topSchool)
                        <div class="rounded-2xl bg-white/10 border border-white/10 p-4 sm:p-5 min-w-0">
                            <p class="text-base sm:text-lg font-black leading-snug break-words">{{ $topSchool->nama_sekolah }}</p>
                            <p class="text-xs text-white/50 mt-1 break-words">{{ $topSchool->jenjang_sekolah ?? '-' }} | {{ $topSchool->UserAccount->email ?? 'Kepala sekolah belum tersedia' }}</p>
                            <div class="grid grid-cols-3 gap-3 mt-5 text-center">
                                <div class="min-w-0">
                                    <p class="text-lg sm:text-xl font-black truncate">{{ $topSchool->total_siswa }}</p>
                                    <p class="text-[10px] text-white/45 font-bold uppercase">Siswa</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-lg sm:text-xl font-black truncate">{{ $topSchool->total_staff }}</p>
                                    <p class="text-[10px] text-white/45 font-bold uppercase">Staff</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-lg sm:text-xl font-black truncate">{{ $topSchool->total_pendapatan > 0 ? number_format($topSchool->total_pendapatan / 1000000, 1, ',', '.') : '0' }}</p>
                                    <p class="text-[10px] text-white/45 font-bold uppercase">Juta Rp</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl bg-white/10 border border-white/10 p-6 text-center text-white/50 font-bold text-sm">
                            Belum ada unit sekolah.
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-[1.5rem] md:rounded-[2rem] p-5 sm:p-6 md:p-8 shadow-sm border border-slate-100 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <div>
                            <p class="text-[#0071BC] text-[11px] sm:text-xs font-black uppercase tracking-[0.2em] mb-2">Ringkasan Unit</p>
                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Status Sekolah</h3>
                        </div>
                        <i class="fas fa-chart-simple text-2xl text-slate-200"></i>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @forelse ($sekolah as $unit)
                            <a href="{{ route('yayasan.school.edit', [$yayasan->id, $unit->id]) }}" class="block rounded-2xl border border-slate-100 bg-slate-50 p-4 hover:bg-blue-50 hover:border-[#0071BC] transition-all min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-black text-sm text-slate-900 leading-snug break-words">{{ $unit->nama_sekolah }}</p>
                                        <p class="text-xs font-bold text-slate-400 mt-1 break-words">{{ $unit->UserAccount->email ?? 'Kepala sekolah belum tersedia' }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full {{ $unit->kepsek_id ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} text-[10px] font-black uppercase shrink-0">
                                        {{ $unit->kepsek_id ? 'Aktif' : 'Kosong' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-6 text-center text-slate-400 font-bold text-sm">
                                Belum ada unit sekolah.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
