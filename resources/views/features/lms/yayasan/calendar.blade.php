@include('components/sidebar-beranda', ['headerSideNav' => 'Kalender & Agenda Yayasan'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
    <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">

        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-600 via-[#0071BC] to-indigo-700 p-8 md:p-10 text-white shadow-xl shadow-blue-100">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/20 blur-2xl"></div>
            <div class="absolute left-1/2 bottom-0 h-28 w-28 rounded-full bg-amber-200/20 blur-xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div>
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white/75">
                            <li>Yayasan</li>
                            <li><i class="fas fa-chevron-right text-[8px]"></i></li>
                            <li class="text-white">Kalender & Agenda</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight">Kalender & Agenda</h1>
                    <p class="text-white/85 mt-3 max-w-2xl font-medium">
                        Pantau agenda akademik semua sekolah di bawah {{ $yayasan->nama_yayasan }} dalam satu tampilan bulanan.
                    </p>
                </div>

                <a href="{{ route('yayasan.dashboard', $yayasan->id) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white text-[#0071BC] rounded-2xl font-black text-sm shadow-lg hover:bg-blue-50 transition-all">
                    <i class="fas fa-arrow-left"></i> Dashboard Yayasan
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('yayasan.calendar', $yayasan->id) }}" class="bg-white rounded-[1.75rem] p-5 md:p-6 shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-4 items-end">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Filter Sekolah</label>
                <select name="school_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
                    <option value="">Semua sekolah</option>
                    @foreach ($sekolahList as $sekolah)
                        <option value="{{ $sekolah->id }}" @selected((string) $selectedSchoolId === (string) $sekolah->id)>
                            {{ $sekolah->nama_sekolah }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Bulan</label>
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-[#0071BC]">
            </div>
            <button class="h-[46px] inline-flex items-center justify-center gap-2 px-6 rounded-2xl bg-[#0071BC] text-white font-black text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Agenda</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $events->count() }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1">agenda pada bulan ini</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Sekolah Terpantau</p>
                <p class="text-4xl font-black text-slate-900 mt-2">{{ $selectedSchoolId ? 1 : $sekolahList->count() }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1">unit dalam filter</p>
            </div>
            <div class="bg-white rounded-[1.5rem] p-5 border border-slate-100 shadow-sm lg:col-span-2">
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Agenda Terpadat</p>
                @php
                    $topSchool = $sekolahList->sortByDesc(fn ($school) => $summaryBySchool[$school->id] ?? 0)->first();
                    $topCount = $topSchool ? (int) ($summaryBySchool[$topSchool->id] ?? 0) : 0;
                @endphp
                <p class="text-xl md:text-2xl font-black text-slate-900 mt-2">{{ $topCount > 0 ? $topSchool->nama_sekolah : 'Belum ada agenda' }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1">{{ $topCount > 0 ? $topCount.' agenda pada bulan ini' : 'Agenda sekolah belum tersedia untuk filter ini' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-[2rem] p-5 md:p-8 shadow-sm border border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Tampilan Bulanan</p>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900">{{ $month->translatedFormat('F Y') }}</h2>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('yayasan.calendar', ['yayasanId' => $yayasan->id, 'school_id' => $selectedSchoolId, 'month' => $prevMonth]) }}" class="w-11 h-11 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:text-[#0071BC] hover:bg-blue-50 transition-all">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('yayasan.calendar', ['yayasanId' => $yayasan->id, 'school_id' => $selectedSchoolId, 'month' => $nextMonth]) }}" class="w-11 h-11 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:text-[#0071BC] hover:bg-blue-50 transition-all">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2 text-center text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-wider mb-3">
                    <div class="text-red-500">Min</div>
                    <div>Sen</div>
                    <div>Sel</div>
                    <div>Rab</div>
                    <div>Kam</div>
                    <div>Jum</div>
                    <div>Sab</div>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    @for ($i = 0; $i < $calendarStartPadding; $i++)
                        <div class="min-h-20 md:min-h-28 rounded-2xl bg-slate-50/40"></div>
                    @endfor

                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = $month->copy()->day($day);
                            $dateKey = $date->format('Y-m-d');
                            $dayEvents = $eventsByDate[$dateKey] ?? collect();
                        @endphp
                        <div class="min-h-20 md:min-h-28 rounded-2xl border p-2 transition-all {{ $dayEvents->isNotEmpty() ? 'bg-blue-50/50 border-blue-100 shadow-sm' : 'bg-white border-slate-100' }}">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-sm font-black {{ $date->isSunday() ? 'text-red-500' : 'text-slate-700' }}">{{ $day }}</span>
                                @if ($dayEvents->isNotEmpty())
                                    <span class="text-[10px] font-black rounded-full bg-[#0071BC] text-white px-2 py-0.5">{{ $dayEvents->count() }}</span>
                                @endif
                            </div>
                            <div class="mt-2 space-y-1 hidden md:block">
                                @foreach ($dayEvents->take(2) as $event)
                                    <div class="truncate rounded-lg px-2 py-1 text-[10px] font-bold bg-white border border-slate-100 text-slate-700" title="{{ $event->title }} - {{ $event->nama_sekolah }}">
                                        <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color: {{ $event->color ?? '#0071BC' }}"></span>{{ $event->title }}
                                    </div>
                                @endforeach
                                @if ($dayEvents->count() > 2)
                                    <p class="text-[10px] font-black text-[#0071BC] px-1">+{{ $dayEvents->count() - 2 }} agenda</p>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-5 md:p-6 shadow-sm border border-slate-100 flex flex-col max-h-[760px]">
                <div class="mb-5 pb-4 border-b border-slate-100">
                    <p class="text-[#0071BC] text-xs font-black uppercase tracking-[0.25em] mb-2">Daftar Agenda</p>
                    <h3 class="text-xl font-black text-slate-900">{{ $month->translatedFormat('F Y') }}</h3>
                </div>

                <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse ($eventsByDate as $date => $dateEvents)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-sm font-black text-slate-900 mb-3">
                                <i class="far fa-calendar-alt text-[#0071BC] mr-2"></i>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                            </p>
                            <div class="space-y-3">
                                @foreach ($dateEvents as $event)
                                    <div class="rounded-xl bg-white p-3 border border-slate-100">
                                        <div class="flex items-start gap-3">
                                            <div class="w-3 h-3 rounded-full mt-1.5 shrink-0" style="background-color: {{ $event->color ?? '#0071BC' }}"></div>
                                            <div>
                                                <p class="font-black text-sm text-slate-800 leading-snug">{{ $event->title }}</p>
                                                <p class="text-xs font-bold text-slate-400 mt-1">{{ $event->nama_sekolah ?? 'Sekolah tidak diketahui' }}</p>
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    <span class="px-2 py-1 rounded-lg bg-blue-50 text-[#0071BC] text-[10px] font-black uppercase">{{ $event->type }}</span>
                                                    <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-black uppercase">{{ $event->status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50">
                            <i class="far fa-calendar-times text-4xl text-slate-300 mb-3"></i>
                            <p class="text-sm font-black text-slate-500">Tidak ada agenda</p>
                            <p class="text-xs font-bold text-slate-400 mt-1">Coba ganti filter sekolah atau bulan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
