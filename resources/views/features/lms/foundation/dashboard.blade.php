@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role === 'Yayasan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-8">
            <main id="container" data-role="{{ $role }}" data-foundation-id="{{ $foundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-building-columns absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>

                        <i class="fa-solid fa-school absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-building-columns text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Beranda Yayasan
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Monitoring aktivitas sekolah, kinerja guru, partisipasi survey, refleksi murid, dan informasi penting lainnya.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mb-6">

                    <!-- Skeleton -->
                    <div id="kpi-loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                        @for ($i = 0; $i < 4; $i++)
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">

                                        <div class="flex-1">
                                            <!-- Title -->
                                            <div class="skeleton h-4 w-24 mb-4"></div>

                                            <!-- Number -->
                                            <div class="skeleton h-9 w-16 mb-4"></div>
                                        </div>

                                        <!-- Icon -->
                                        <div class="skeleton w-14 h-14 rounded-xl"></div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <!-- Content -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                            <!-- TOTAL SEKOLAH -->
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Sekolah
                                        </p>

                                        <h2 id="total-school"
                                            class="mt-2 text-xl font-bold text-slate-800">
                                            0
                                        </h2>
                                    </div>

                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-100">
                                        <i class="fa-solid fa-school text-xl text-cyan-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- TOTAL GURU -->
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Guru
                                        </p>

                                        <h2 id="total-teacher"
                                            class="mt-2 text-xl font-bold text-slate-800">
                                            0
                                        </h2>
                                    </div>

                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                                        <i class="fa-solid fa-chalkboard-user text-xl text-blue-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- TOTAL SISWA -->
                            <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Siswa
                                        </p>

                                        <h2 id="total-student"
                                            class="mt-2 text-xl font-bold text-slate-800">
                                            0
                                        </h2>
                                    </div>

                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100">
                                        <i class="fa-solid fa-user-graduate text-xl text-violet-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- TOTAL ORANG TUA -->
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Orang Tua
                                        </p>

                                        <h2 id="total-parent"
                                            class="mt-2 text-xl font-bold text-slate-800">
                                            0
                                        </h2>
                                    </div>

                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                                        <i class="fa-solid fa-people-roof text-xl text-amber-600"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- QUICK ACCESS -->
                <section class="mb-10">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                        <!-- KINERJA GURU -->
                        <a href="{{ route('lms.foundation.teacher-performance.view', [
                            'role' => Auth::user()->role,
                            'foundationId' => Auth::user()->SchoolFoundationProfile->school_foundation_id,
                        ]) }}"
                            class="group rounded-2xl border border-violet-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-violet-300 hover:shadow-xl">

                            <div class="flex items-start justify-between">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50">
                                    <i class="fa-solid fa-chalkboard-user text-2xl text-violet-600"></i>
                                </div>
                            </div>

                            <h3 class="mt-5 text-lg font-bold text-slate-800">
                                Kinerja Guru
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Pantau assessment, materi pembelajaran, serta aktivitas guru pada seluruh sekolah.
                            </p>

                            <div class="mt-6 w-[90%] group-hover:w-full inline-flex items-center justify-between px-4 py-3 rounded-2xl bg-[#EFF6FF] text-violet-600 font-black text-sm 
                                group-hover:bg-violet-600 group-hover:text-white transition-all ease-out duration-800">

                                <span>Monitoring Guru</span>

                                <div class="w-8 h-8 rounded-xl bg-white text-violet-600 flex items-center justify-center transition-all duration-500 group-hover:bg-white/20
                                    group-hover:text-white group-hover:translate-x-1.5">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </div>
                            </div>
                        </a>

                        <!-- REFLEKSI -->
                        <a href="{{ route('lms.foundation.student-reflection.view', [
                            'role' => Auth::user()->role,
                            'foundationId' => Auth::user()->SchoolFoundationProfile->school_foundation_id,
                        ]) }}"
                            class="group rounded-2xl border border-pink-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-pink-300 hover:shadow-xl">

                            <div class="flex items-start justify-between">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-50">
                                    <i class="fa-solid fa-face-smile text-2xl text-pink-600"></i>
                                </div>
                            </div>

                            <h3 class="mt-5 text-lg font-bold text-slate-800">
                                Refleksi
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Lihat ringkasan kondisi emosional siswa berdasarkan hasil refleksi harian di seluruh sekolah.
                            </p>

                            <div class="mt-6 w-[90%] group-hover:w-full inline-flex items-center justify-between px-4 py-3 rounded-2xl bg-[#EFF6FF] text-pink-600 font-black text-sm 
                                group-hover:bg-pink-600 group-hover:text-white transition-all ease-out duration-800">

                                <span>Lihat Refleksi</span>

                                <div class="w-8 h-8 rounded-xl bg-white text-pink-600 flex items-center justify-center transition-all duration-500 group-hover:bg-white/20
                                    group-hover:text-white group-hover:translate-x-1.5">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </div>
                            </div>
                        </a>

                        <!-- WARGA SEKOLAH -->
                        <a href="{{ route('lms.foundation.school-users.view', [
                            'role' => Auth::user()->role,
                            'foundationId' => Auth::user()->SchoolFoundationProfile->school_foundation_id,
                        ]) }}"
                            class="group rounded-2xl border border-amber-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl">

                            <div class="flex items-start justify-between">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50">
                                    <i class="fa-solid fa-users text-2xl text-amber-600"></i>
                                </div>
                            </div>

                            <h3 class="mt-5 text-lg font-bold text-slate-800">
                                Pengguna Sekolah
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Monitoring seluruh pengguna pada setiap sekolah di bawah naungan yayasan.
                            </p>

                            <div class="mt-6 w-[90%] group-hover:w-full inline-flex items-center justify-between px-4 py-3 rounded-2xl bg-[#EFF6FF] text-amber-600 font-black text-sm 
                                group-hover:bg-amber-600 group-hover:text-white transition-all ease-out duration-800">

                                <span>Lihat Pengguna</span>

                                <div class="w-8 h-8 rounded-xl bg-white text-amber-600 flex items-center justify-center transition-all duration-500 group-hover:bg-white/20
                                    group-hover:text-white group-hover:translate-x-1.5">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </div>
                            </div>
                        </a>

                        <!-- KEUANGAN SEKOLAH -->
                        <button type="button" onclick="finance_modal.showModal()" class="group text-left rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm 
                            transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl cursor-pointer">

                            <div class="flex items-start justify-between">

                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50">
                                    <i class="fa-solid fa-wallet text-2xl text-emerald-600"></i>
                                </div>

                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all duration-300 
                                    group-hover:bg-emerald-50 group-hover:text-emerald-600">

                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </div>

                            </div>

                            <h3 class="mt-5 text-lg font-bold text-slate-800">
                                Keuangan Sekolah
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Akses sistem keuangan seluruh sekolah yang berada di bawah naungan yayasan.
                            </p>

                            <div class="mt-6 w-[90%] group-hover:w-full inline-flex items-center justify-between px-4 py-3 rounded-2xl 
                                bg-emerald-50 text-emerald-600 font-black text-sm
                                group-hover:bg-emerald-600 group-hover:text-white
                                transition-all ease-out duration-300">

                                <span>Lihat Link Keuangan</span>

                                <div class="w-8 h-8 rounded-xl bg-white text-emerald-600 flex items-center justify-center 
                                    transition-all duration-300
                                    group-hover:bg-white/20 group-hover:text-white group-hover:translate-x-1.5">

                                    <i class="fas fa-arrow-right text-xs"></i>

                                </div>
                            </div>
                        </button>
                    </div>
                </section>

                <!-- MODAL LINK KEUANGAN -->
                <dialog id="finance_modal" class="modal">
                    <div class="modal-box max-w-lg p-0 overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                        <i class="fa-solid fa-wallet text-lg text-emerald-600"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-base font-bold text-slate-800">
                                            Keuangan Sekolah
                                        </h3>

                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Akses sistem keuangan sekolah
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button aria-colindex=""class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 
                                        hover:text-slate-600 cursor-pointer">

                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-3">
                                <i class="fa-solid fa-circle-info mt-0.5 text-sm text-emerald-600"></i>

                                <p class="text-xs leading-relaxed text-slate-600">
                                    Pilih sekolah untuk membuka sistem keuangannya.
                                </p>
                            </div>

                            <div class="max-h-[55vh] space-y-3 overflow-y-auto pr-1">
                                @forelse ($financeAccessLink as $item)
                                    <div class="group flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 transition-all duration-200 
                                        hover:border-emerald-200 hover:bg-emerald-50/40">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition 
                                                group-hover:bg-emerald-100 group-hover:text-emerald-600">

                                                @if ($item->SchoolPartner?->logo && file_exists(public_path($item->SchoolPartner->logo)))
                                                    <img src="{{ asset($item->SchoolPartner->logo) }}"
                                                        alt="{{ $item->SchoolPartner->nama_sekolah ?? 'Logo sekolah' }}" class="h-full w-full object-cover">
                                                @else
                                                    <i class="fa-solid fa-school"></i>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-700">
                                                    {{ $item->SchoolPartner->nama_sekolah ?? '-' }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-slate-400">
                                                    Sistem Keuangan Sekolah
                                                </p>
                                            </div>
                                        </div>

                                        <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-all duration-200 
                                            hover:bg-emerald-600 hover:text-white">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    </div>

                                <!-- EMPTY STATE -->
                                @empty
                                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                            <i class="fa-solid fa-building-columns text-lg"></i>
                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-slate-700">
                                            Belum Ada Link Keuangan
                                        </h3>

                                        <p class="mt-1 max-w-sm text-xs leading-relaxed text-slate-500">
                                            Belum ada sekolah di bawah naungan yayasan yang memiliki akses ke sistem keuangan.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                            <p class="text-xs text-slate-400">
                                {{ $financeAccessLink->count() }} sekolah tersedia
                            </p>

                            <form method="dialog">
                                <button
                                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 cursor-pointer">

                                    Tutup

                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- CLICK OUTSIDE -->
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>

                </dialog>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/foundation/dashboard/load-kpi.js') }}"></script> <!--- dashboard kpi ---->