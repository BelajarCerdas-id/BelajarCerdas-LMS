@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role == 'Administrator')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out z-20 pb-20">
        <div class="mt-4 sm:mt-6 mx-4 sm:mx-8 space-y-6">

            <!-- HERO -->
            <section>
                <div class="rounded-3xl p-4 sm:p-5 text-white shadow-xl overflow-hidden relative border border-white/10 bg-[#0071BC]" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 60%, #002b49 100%) !important;">
                    <div class="absolute right-0 top-0 opacity-10 pointer-events-none select-none">
                        <i class="fa-solid fa-gauge-high text-[180px] translate-x-12 -translate-y-8"></i>
                    </div>

                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-lg sm:text-xl font-extrabold tracking-tight">
                                Admin Command Center
                            </h1>
                            <p class="mt-1 text-sky-100/90 text-xs sm:text-sm max-w-2xl leading-snug">
                                Ringkasan sekolah mitra, pengguna, aktivitas, konten, dan kesehatan ujian dalam satu halaman.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('lms.office.analytics.view', ['role' => $role]) }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-medium text-xs transition-all border border-white/20">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Site Analytics</span>
                            </a>
                            <a href="{{ route('lms.office.assessmentDebug.view', ['role' => $role]) }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-medium text-xs transition-all border border-white/20">
                                <i class="fa-solid fa-bug"></i>
                                <span>Exam Debugger</span>
                            </a>
                            <a href="{{ route('lms.schoolSubscription.view', ['role' => $role]) }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-medium text-xs transition-all border border-white/20">
                                <i class="fa-solid fa-school"></i>
                                <span>School Partner</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI -->
            <section>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sekolah Mitra</p>
                                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($schools) }}</h2>
                                <p class="text-xs text-slate-500 mt-2"><strong class="text-slate-700">{{ number_format($activeContracts) }}</strong> kontrak aktif</p>
                                <p class="text-[11px] mt-1 {{ $expiringContracts > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">
                                    <i class="fa-regular fa-clock"></i> {{ number_format($expiringContracts) }} berakhir &le; 30 hari
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-school"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                    </div>

                    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pengguna</p>
                                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($activeUsers) }}</h2>
                                <p class="text-xs text-slate-500 mt-2">aktif dari <strong class="text-slate-700">{{ number_format($totalUsers) }}</strong> akun</p>
                                <p class="text-[11px] mt-1 text-emerald-600 font-semibold">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    {{ number_format($onlineUsers) }} online sekarang
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#0071BC] to-sky-400"></div>
                    </div>

                    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Aktivitas Hari Ini</p>
                                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($activitiesToday) }}</h2>
                                <p class="text-xs text-slate-500 mt-2">akses modul &amp; halaman</p>
                                <p class="text-[11px] mt-1 text-slate-400">
                                    <i class="fa-solid fa-school text-amber-500"></i> {{ number_format($activeSchoolsToday) }} sekolah aktif
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    </div>

                    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Ujian Hari Ini</p>
                                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($attemptsToday) }}</h2>
                                <p class="text-xs text-slate-500 mt-2">sesi dimulai hari ini</p>
                                <p class="text-[11px] mt-1 text-red-600 font-semibold">
                                    <i class="fa-solid fa-shield-halved"></i> {{ number_format($blockedAttempts) }} terblokir
                                    <span class="text-slate-400 font-normal">&middot; {{ number_format($inProgress) }} berlangsung</span>
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-file-pen"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                    </div>

                </div>
            </section>

            <!-- SECOND ROW -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm">
                    <div class="pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-users text-[#0071BC]"></i>
                            <span>Komposisi Role</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Akun terdaftar per peran</p>
                    </div>

                    <div class="mt-4 space-y-3">
                        @php $maxRole = max(1, (int) $roleCounts->max('total')); @endphp
                        @foreach ($roleCounts as $row)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700">{{ $row->role }}</span>
                                    <span class="text-slate-500">{{ number_format($row->total) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-2 rounded-full bg-[#0071BC]" style="width: {{ round(($row->total / $maxRole) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col">
                    <div class="pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-[#0071BC]"></i>
                            <span>Konten &amp; Soal</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Bahan ajar yang tersedia di platform</p>
                    </div>

                    <div class="flex-1 mt-2">
                        <div class="flex items-center justify-between py-3 border-b border-slate-100">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Materi Aktif</p>
                                <p class="text-xs text-slate-400">Bank materi</p>
                            </div>
                            <span class="text-lg font-bold text-slate-900">{{ number_format($contents) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-slate-100">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Bank Soal</p>
                                <p class="text-xs text-slate-400">Soal sekolah aktif</p>
                            </div>
                            <span class="text-lg font-bold text-slate-900">{{ number_format($questions) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Assessment</p>
                                <p class="text-xs text-slate-400">Total assessment dibuat</p>
                            </div>
                            <span class="text-lg font-bold text-slate-900">{{ number_format($assessments) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                        <a href="{{ route('lms.contentManagement.view.noSchoolPartner', ['role' => $role]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200 transition">
                            <i class="fa-solid fa-book-open"></i> Materi
                        </a>
                        <a href="{{ route('lms.questionBankManagement.view.noSchoolPartner', ['role' => $role]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200 transition">
                            <i class="fa-solid fa-circle-question"></i> Bank Soal
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm">
                    <div class="pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                            <span>Perlu Perhatian</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kontrak berakhir atau belum aktif</p>
                    </div>

                    <div class="mt-1">
                        @forelse ($attentionSchools as $school)
                            <div class="flex items-start justify-between gap-3 py-3 border-b border-slate-100">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $school->nama_sekolah }}</p>
                                    <p class="text-xs text-slate-400">NPSN {{ $school->npsn }}</p>
                                </div>
                                @if ($school->active_contracts == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                        Tanpa kontrak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-100 whitespace-nowrap">
                                        s/d {{ \Carbon\Carbon::parse($school->next_end_contract)->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-slate-400">Semua sekolah dalam kondisi baik.</p>
                        @endforelse
                    </div>

                    <a href="{{ route('lms.schoolSubscription.view', ['role' => $role]) }}"
                        class="inline-flex items-center gap-1.5 mt-3 text-xs font-medium text-[#0071BC] hover:underline">
                        Lihat semua sekolah <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </section>

            <!-- RECENT ACTIVITY -->
            <section class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                            <span>Aktivitas Terbaru</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Akses terakhir pengguna di seluruh platform</p>
                    </div>
                    <a href="{{ route('lms.office.analytics.view', ['role' => $role]) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-[#0071BC] hover:underline">
                        Buka Site Analytics <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                                <th class="py-2 pr-3 font-semibold">Pengguna</th>
                                <th class="py-2 pr-3 font-semibold">Sekolah</th>
                                <th class="py-2 pr-3 font-semibold">Modul</th>
                                <th class="py-2 pr-3 font-semibold">Aksi</th>
                                <th class="py-2 font-semibold">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentActivities as $activity)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2.5 pr-3">
                                        <p class="font-medium text-slate-800">{{ $activity->email ?? 'Sistem' }}</p>
                                        <p class="text-xs text-slate-400">{{ $activity->role }}</p>
                                    </td>
                                    <td class="py-2.5 pr-3 text-slate-600">{{ $activity->nama_sekolah ?? 'Belajar Cerdas Office' }}</td>
                                    <td class="py-2.5 pr-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                            {{ $activity->module }}
                                        </span>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $activity->sub_module }}</p>
                                    </td>
                                    <td class="py-2.5 pr-3 text-slate-500">{{ $activity->action }}</td>
                                    <td class="py-2.5 text-slate-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('d M H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada aktivitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>
@endif
