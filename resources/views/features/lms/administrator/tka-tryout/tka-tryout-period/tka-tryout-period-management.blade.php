@include('components/sidebar-beranda', ['headerSideNav' => 'Tryout TKA']);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}">

                <!-- ALERTS -->
                <div id="alert-success-update-tka-tryout-period"></div>
                <div id="alert-success-update-tka-tryout-period-school-override"></div>
                <div id="alert-success-update-tka-tryout-session"></div>
                <div id="alert-success-update-tka-tryout-subject"></div>
                
                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">
                        <i class="fa-solid fa-calendar-days absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>
                        <i class="fa-solid fa-clipboard-check absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 lg:gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">
                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-clipboard-check text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Manajemen Tryout TKA
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Atur periode, sesi, dan mata pelajaran Tryout TKA untuk setiap tahun ajaran.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="w-full lg:w-auto">
                                <a href="{{ route('lms.office.tka-tryout-period-form.view', [
                                    'role' => $role,
                                ]) }}">
                                    <button class="btn w-full lg:w-auto bg-white border-white text-[#005A9C] hover:bg-slate-100 hover:border-slate-100 shadow-lg">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Periode TKA
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FILTER -->
                <section class="mb-6">

                    <!-- FILTER SKELETON -->
                    <div id="filter-tka-tryout-skeleton">
                        <div class="border border-slate-300 bg-slate-50 px-6 py-5 rounded-xl">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-11 w-11 shrink-0 rounded-xl bg-slate-300 animate-pulse"></div>

                                <div class="flex-1">
                                    <div class="h-4 w-32 rounded bg-slate-300 animate-pulse"></div>
                                    <div class="mt-2 h-3 w-72 max-w-full rounded bg-slate-300 animate-pulse"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                @for ($i = 1; $i < 1; $i++)
                                    <div>
                                        <div class="mb-2 h-4 w-24 rounded bg-slate-300 animate-pulse"></div>

                                        <div class="relative">
                                            <div class="h-12 w-full rounded-xl border border-slate-300 bg-slate-200 animate-pulse"></div>

                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-300">
                                                <div class="h-4 w-4 rounded bg-slate-400 animate-pulse"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor

                                <div>
                                    <div class="mb-2 h-4 w-16 rounded bg-slate-300 animate-pulse"></div>

                                    <div class="relative">
                                        <div class="h-12 w-full rounded-xl border border-slate-300 bg-slate-200 animate-pulse"></div>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-300">
                                            <div class="h-4 w-4 rounded bg-slate-400 animate-pulse"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER CONTENT -->
                    <div id="filter-tka-tryout-content" class="hidden">
                        <div class="border border-gray-300 bg-slate-50 px-6 py-5 rounded-xl">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-filter text-blue-600"></i>
                                </div>

                                <div>
                                    <h4 class="text-base font-semibold text-slate-800">
                                        Filter Periode
                                    </h4>

                                    <p class="text-sm text-slate-500">
                                        Gunakan filter untuk menemukan periode Tryout TKA dengan lebih cepat.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                <!-- Tahun Ajaran -->
                                <div>
                                    <label for="search-academic-year" class="mb-2 block text-sm font-medium text-slate-700">
                                        Tahun Ajaran
                                    </label>

                                    <div class="relative">
                                        <select id="search-academic-year" class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 
                                            text-sm text-slate-700 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 
                                            outline-none cursor-pointer">
                                            
                                            <option value="" class="hidden">
                                                Semua Tahun Ajaran
                                            </option>
                                        </select>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                            <i class="fa-solid fa-calendar-days text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SUMMARY -->
                <section class="mb-6">

                    <!-- SUMMARY SKELETON -->
                    <div id="kpi-loading">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="h-4 w-24 rounded bg-slate-300 animate-pulse"></div>

                                            <div class="mt-2 h-8 w-12 rounded bg-slate-300 animate-pulse"></div>

                                            <div class="mt-2 h-3 w-36 max-w-full rounded bg-slate-200 animate-pulse"></div>
                                        </div>

                                        <div class="h-11 w-11 shrink-0 rounded-xl bg-slate-300 animate-pulse"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- SUMMARY CONTENT -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <!-- Total Periode -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Periode
                                        </p>

                                        <h3 id="total-tka-tryout-period" class="mt-2 text-2xl font-bold text-slate-800">
                                            0
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Seluruh periode Tryout TKA
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <i class="fa-solid fa-layer-group text-lg text-blue-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Periode Default -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Periode Default
                                        </p>

                                        <h3 id="total-tka-tryout-period-default" class="mt-2 text-2xl font-bold text-slate-800">
                                            0
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Periode utama untuk sekolah
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                        <i class="fa-solid fa-calendar-check text-lg text-emerald-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Periode Custom -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Periode Custom
                                        </p>

                                        <h3 id="total-tka-tryout-period-override" class="mt-2 text-2xl font-bold text-slate-800">
                                            0
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Periode khusus sekolah
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50">
                                        <i class="fa-solid fa-sliders text-lg text-amber-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Sekolah Override -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Sekolah dengan Periode Khusus
                                        </p>

                                        <h3 id="total-tka-tryout-school-override" class="mt-2 text-2xl font-bold text-slate-800">
                                            0
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Sekolah menggunakan periode custom
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-50">
                                        <i class="fa-solid fa-school text-lg text-purple-600"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- TRYOUT TKA PERIOD LIST -->
                <section class="mb-6 rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm lg:p-6">
                    <!-- SECTION HEADER -->
                    <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-calendar-days text-blue-600"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Daftar Periode Tryout TKA
                                    </h2>

                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Kelola periode Tryout TKA dan konfigurasi jadwal yang digunakan oleh sekolah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE CONTENT -->
                    <div id="table-content" class="overflow-x-auto">
                        <table id="table-tka-tryout-period-list" class="min-w-full border-collapse text-sm">

                            <!-- SKELETON TABLE HEADER -->
                            <thead id="thead-tka-tryout-period-list-skeleton">
                                <tr class="bg-slate-50">
                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-24 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- THEAD TABLE CONTENT -->
                            <thead id="thead-tka-tryout-period-list" class="hidden">
                                <tr class="bg-slate-50">
                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        PERIODE
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        PELAKSANAAN
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        SESI
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        MATA PELAJARAN
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        AKSI
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        PERIODE KHUSUS
                                    </th>
                                </tr>
                            </thead>

                            <!-- SKELETON TABLE BODY -->
                            <tbody id="tbody-tka-tryout-period-list-skeleton">

                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                        </td>

                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto flex max-w-45 flex-col items-center gap-2">
                                                <div class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
                                                <div class="h-3 w-3/4 animate-pulse rounded bg-slate-200"></div>
                                            </div>
                                        </td>

                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-7 w-12 animate-pulse rounded-lg bg-slate-200"></div>
                                        </td>

                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-7 w-12 animate-pulse rounded-lg bg-slate-200"></div>
                                        </td>

                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-7 w-12 animate-pulse rounded-lg bg-slate-200"></div>
                                        </td>

                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-8 w-20 animate-pulse rounded-lg bg-slate-200"></div>
                                        </td>
                                    </tr>
                                @endfor

                            </tbody>

                            <!-- TBODY TABLE CONTENT -->
                            <tbody id="tbody-tka-tryout-period-list" class="hidden">
                                <!-- AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <!-- EMPTY MESSAGE -->
                    <div id="empty-message-tka-tryout-period-list" class="hidden rounded-2xl border-2 border-dashed border-gray-300 bg-base-100 py-20">
                        <div class="mx-auto flex w-full max-w-md flex-col items-center text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                                <i class="fa-solid fa-clipboard-check text-3xl text-primary"></i>
                            </div>

                            <h3 class="mt-6 text-xl font-semibold">
                                Belum Ada Data Periode Tryout TKA.
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-base-content/60">
                                Belum ada data periode Tryout TKA yang tercatat pada tahun ajaran yang dipilih.
                            </p>
                        </div>
                    </div>

                    <!-- PAGINATION -->
                    <div class="pagination-container-tka-tryout-period-list mt-5"></div>
                </section>

                <!-- MODAL DETAIL SESI -->
                <dialog id="modal-tka-tryout-session" class="modal">
                    <div class="modal-box w-11/12 max-w-4xl rounded-2xl p-0">

                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                                        <i class="fa-solid fa-clock text-emerald-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Daftar Sesi Tryout TKA
                                        </h3>

                                        <p id="tka-tryout-session-period-information" class="mt-0.5 text-sm text-slate-500">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="max-h-[65vh] overflow-y-auto px-6 py-5">

                            <!-- INFO -->
                            <div id="tka-tryout-session-info" class="mb-6 flex flex-col gap-3 border border-blue-100 bg-blue-50 px-4 py-3 sm:flex-row sm:items-center 
                                sm:justify-between">

                                <div>
                                    <p id="tka-tryout-session-total" class="text-sm font-semibold text-slate-700">
                                        0 Sesi
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        Jadwal sesi Tryout TKA yang tersedia dalam periode ini.
                                    </p>
                                </div>

                                <a id="button-add-tka-tryout-session" href="">
                                    <button type="button" class="btn btn-sm w-full rounded-lg border-[#0071BC] bg-[#0071BC] 
                                        text-white hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Sesi
                                    </button>
                                </a>
                            </div>

                            <!-- SESSION CONTENT -->
                            <div id="tka-tryout-session-content">

                                <!-- SKELETON LOADING -->
                                <div id="tka-tryout-session-list-skeleton">

                                    <!-- DATE SKELETON 1 -->
                                    <div class="mb-6">

                                        <div class="mb-3 flex items-center gap-2">
                                            <div class="h-9 w-9 shrink-0 animate-pulse rounded-lg bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-48 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="overflow-hidden rounded-xl border border-slate-200">

                                            <!-- SESSION SKELETON -->
                                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>

                                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>

                                            <div class="flex items-center justify-between gap-4 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SESSION LIST -->
                                <div id="tka-tryout-session-list" class="hidden">
                                    <!-- AJAX -->
                                </div>

                                <!-- EMPTY MESSAGE -->
                                <div id="empty-message-tka-tryout-session" class="hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-14">
                                    <div class="mx-auto flex max-w-md flex-col items-center text-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100">
                                            <i class="fa-regular fa-clock text-2xl text-emerald-600"></i>
                                        </div>

                                        <h3 class="mt-5 text-base font-semibold text-slate-700">
                                            Belum Ada Sesi Tryout
                                        </h3>

                                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                                            Belum ada sesi yang dijadwalkan pada periode ini.
                                            Tambahkan sesi untuk menentukan waktu pelaksanaan Tryout TKA.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FOOTER -->
                        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                            <form method="dialog">
                                <button type="submit" class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                    Tutup
                                </button>
                            </form>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">
                            close
                        </button>
                    </form>
                </dialog>

                <!-- MODAL ASSIGN MATA PELAJARAN TRYOUT TKA (NON OVERRIDE) -->
                <dialog id="modal-tka-tryout-subject" class="modal">
                    <div class="modal-box w-11/12 max-w-3xl max-h-[85vh] overflow-y-auto rounded-2xl bg-white p-0">

                        <!-- MODAL HEADER -->
                        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-5 py-5 sm:px-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                                        <i class="fa-solid fa-book-open text-amber-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Mata Pelajaran Tryout
                                        </h3>

                                        <p id="tka-tryout-subject-information" class="mt-0.5 text-sm text-slate-500">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button id="button-close-tka-tryout-subject" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 
                                        text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-5 py-5 sm:px-6">

                            <!-- SUBJECT ACTION -->
                            <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                                <i class="fa-solid fa-book text-sm text-[#0071BC]"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <p id="tka-tryout-subject-total" class="text-sm font-bold text-slate-800">
                                                    0 Mata Pelajaran
                                                </p>

                                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                                                    Mata pelajaran yang telah terdaftar pada periode Tryout TKA ini.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="" id="button-assign-tka-tryout-subject">
                                        <button type="button" class="btn w-full whitespace-nowrap rounded-xl border-[#0071BC] 
                                            bg-[#0071BC] text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                            <i class="fa-solid fa-plus"></i>
                                            Tambah Mata Pelajaran
                                        </button>
                                    </a>
                                </div>
                            </div>

                            <!-- SUBJECT LIST SKELETON -->
                            <div id="tka-tryout-subject-list-skeleton" class="overflow-hidden rounded-xl border border-slate-200">
                                @for ($i = 0; $i < 4; $i++)
                                    <div @class(['flex items-center gap-3 px-4 py-4', 'border-b border-slate-200' => $i < 3])>
                                        <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                        <div class="min-w-0 flex-1">
                                            <div class="h-4 w-40 animate-pulse rounded bg-slate-200"></div>
                                            <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- SUBJECT LIST -->
                            <div id="tka-tryout-subject-list" class="hidden overflow-hidden rounded-xl border border-slate-300">
                                <!-- SHOW DATA IN AJAX -->
                            </div>

                            <!-- EMPTY SUBJECT -->
                            <div id="empty-message-tka-tryout-subject" class="hidden overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-12">
                                <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200">
                                        <i class="fa-solid fa-book-open text-lg text-slate-400"></i>
                                    </div>

                                    <h4 class="mt-4 text-sm font-bold text-slate-700">
                                        Belum Ada Mata Pelajaran
                                    </h4>

                                    <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                        Belum ada mata pelajaran yang ditambahkan ke periode Tryout TKA ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FOOTER -->
                        <div class="sticky bottom-0 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                                <form method="dialog">
                                    <button id="button-cancel-tka-tryout-subject" class="btn w-full rounded-xl border-slate-300 bg-white 
                                        text-slate-600 shadow-sm hover:border-slate-400 hover:bg-slate-100 sm:w-auto">
                                        <i class="fa-solid fa-xmark"></i>
                                        Tutup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- MODAL ASSIGN MATA PELAJARAN TRYOUT TKA (OVERRIDE SEKOLAH) -->
                <dialog id="modal-tka-tryout-override-subject" class="modal">
                    <div class="modal-box w-11/12 max-w-3xl max-h-[85vh] overflow-y-auto rounded-2xl bg-white p-0">

                        <!-- MODAL HEADER -->
                        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-5 py-5 sm:px-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100">
                                        <i class="fa-solid fa-book-open text-purple-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Mata Pelajaran Tryout
                                        </h3>

                                        <p id="tka-tryout-override-subject-information" class="mt-0.5 truncate text-sm text-slate-500">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button id="button-close-tka-tryout-override-subject" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 
                                        hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-5 py-5 sm:px-6">

                            <!-- SUBJECT ACTION -->
                            <div class="mb-5 rounded-xl border border-purple-100 bg-purple-50 px-4 py-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                                <i class="fa-solid fa-book text-sm text-[#0071BC]"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <p id="tka-tryout-override-subject-total" class="text-sm font-bold text-slate-800">
                                                    0 Mata Pelajaran
                                                </p>

                                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                                                    Mata pelajaran yang telah terdaftar untuk periode khusus sekolah ini.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="" id="button-assign-tka-tryout-override-subject">
                                        <button type="button" class="btn w-full whitespace-nowrap rounded-xl border-[#0071BC] 
                                            bg-[#0071BC] text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">

                                            <i class="fa-solid fa-plus"></i>
                                            Tambah Mata Pelajaran
                                        </button>
                                    </a>
                                </div>
                            </div>

                            <!-- SUBJECT LIST SKELETON -->
                            <div id="tka-tryout-override-subject-list-skeleton" class="overflow-hidden rounded-xl border border-slate-200">
                                @for ($i = 0; $i < 4; $i++)
                                    <div @class(['flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between', 'border-b border-slate-200' => $i < 3])>
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-40 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="h-6 w-16 animate-pulse rounded-full bg-slate-100"></div>
                                    </div>
                                @endfor
                            </div>

                            <!-- SUBJECT LIST -->
                            <div id="tka-tryout-override-subject-list" class="hidden overflow-hidden rounded-xl border border-slate-300">
                                <!-- SHOW DATA IN AJAX -->
                            </div>

                            <!-- EMPTY SUBJECT -->
                            <div id="empty-message-tka-tryout-override-subject" class="hidden overflow-hidden rounded-xl border border-dashed border-slate-300 
                                bg-slate-50 px-5 py-12">
                                <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200">
                                        <i class="fa-solid fa-book-open text-lg text-slate-400"></i>
                                    </div>

                                    <h4 class="mt-4 text-sm font-bold text-slate-700">
                                        Belum Ada Mata Pelajaran
                                    </h4>

                                    <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                        Belum ada mata pelajaran yang ditambahkan untuk periode khusus sekolah ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FOOTER -->
                        <div class="sticky bottom-0 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                                <form method="dialog">
                                    <button id="button-cancel-tka-tryout-override-subject" class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 
                                        shadow-sm hover:border-slate-400 hover:bg-slate-100 sm:w-auto">
                                        
                                        <i class="fa-solid fa-xmark"></i>
                                        Tutup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- MODAL EDIT MATA PELAJARAN TRYOUT TKA -->
                <dialog id="modal-edit-tka-tryout-subject" class="modal">
                    <div class="modal-box w-11/12 max-w-2xl rounded-2xl p-0">
                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                        <i class="fa-solid fa-pen-to-square text-[#0071BC]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Edit Mata Pelajaran
                                        </h3>
                                        <p class="mt-0.5 text-sm text-slate-500">
                                            Perbarui tanggal, jumlah soal, dan durasi mata pelajaran Tryout TKA.
                                        </p>
                                    </div>
                                </div>
                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-6 py-5">
                            <!-- SUBJECT INFORMATION -->
                            <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3.5">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                        <i class="fa-solid fa-book-open text-sm text-[#0071BC]"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-blue-500">
                                            Mata Pelajaran
                                        </p>

                                        <p id="edit-tka-tryout-subject-name" class="mt-0.5 truncate text-sm font-bold text-blue-800">
                                            -
                                        </p>

                                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-blue-600">
                                            <span id="edit-tka-tryout-subject-class">
                                                -
                                            </span>

                                            <i class="fa-solid fa-circle text-[3px] text-blue-300"></i>

                                            <span>
                                                TKA
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="edit-tka-tryout-subject-form">
                                <input type="hidden" id="edit-tka-tryout-subject-id" name="tka_tryout_subject_id" value="">
                                <input type="hidden" id="edit-tka-tryout-subject-period-id" name="period_id" value="">
                                <input type="hidden" id="edit-tka-tryout-subject-period-override-id" name="period_override_id" value="">
                                <input type="hidden" id="edit-tka-tryout-subject-is-override" name="is_override" value="0">

                                <!-- CONFIGURATION -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Konfigurasi Mata Pelajaran
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Atur tanggal pelaksanaan, jumlah soal, dan durasi pengerjaan.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        <!-- TANGGAL PELAKSANAAN -->
                                        <div class="sm:col-span-2">
                                            <label for="edit-tka-tryout-subject-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Pelaksanaan
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="edit-tka-tryout-subject-date" name="subject_date" value="" class="w-full rounded-xl border-2 
                                                    border-slate-200 bg-white px-3 py-3.5 pr-10 text-sm text-slate-700 shadow-sm outline-none transition duration-200 
                                                    hover:border-slate-300 focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100" placeholder="Pilih tanggal pelaksanaan" 
                                                    autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>

                                            <p id="error-edit-tka-tryout-subject-date" class="mt-1 hidden text-xs font-semibold text-red-500"></p>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal ketika mata pelajaran akan diujikan.
                                            </p>
                                        </div>

                                        <!-- JUMLAH SOAL -->
                                        <div>
                                            <label for="edit-tka-tryout-subject-total-question" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jumlah Soal
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="number" id="edit-tka-tryout-subject-total-question" name="total_question" min="1" value="" class="w-full rounded-xl 
                                                    border border-slate-300 bg-white px-3 py-3.5 pr-16 text-sm text-slate-700 shadow-sm outline-none 
                                                    transition duration-200 focus:border-[#0071BC] focus:ring-1 focus:ring-[#0071BC]" placeholder="Contoh: 30" 
                                                    autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">
                                                    soal
                                                </span>
                                            </div>

                                            <p id="error-edit-tka-tryout-subject-total-question" class="mt-1 hidden text-xs font-semibold text-red-500"></p>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Jumlah soal yang akan dikerjakan siswa.
                                            </p>
                                        </div>

                                        <!-- DURASI -->
                                        <div>
                                            <label for="edit-tka-tryout-subject-duration" class="mb-2 block text-sm font-medium text-slate-700">
                                                Durasi
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="number" id="edit-tka-tryout-subject-duration" name="duration" min="1" value="" class="w-full rounded-xl border 
                                                    border-slate-300 bg-white px-3 py-3.5 pr-16 text-sm text-slate-700 shadow-sm outline-none transition duration-200 
                                                    focus:border-[#0071BC] focus:ring-1 focus:ring-[#0071BC]" placeholder="Contoh: 60" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">
                                                    menit
                                                </span>
                                            </div>

                                            <p id="error-edit-tka-tryout-subject-duration" class="mt-1 hidden text-xs font-semibold text-red-500"></p>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu pengerjaan dalam menit.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FORM ERROR -->
                                <p id="error-edit-tka-tryout-subject" class="mb-4 hidden text-xs font-semibold text-red-500"></p>

                                <!-- FORM ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button"
                                        id="button-cancel-edit-tka-tryout-subject"
                                        class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                        Batal
                                    </button>

                                    <button type="button"
                                        id="button-update-tka-tryout-subject"
                                        class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- MODAL BACKDROP -->
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>
                
                <!-- MODAL SEKOLAH OVERRIDE -->
                <dialog id="modal-tka-tryout-override" class="modal">
                    <div class="modal-box w-11/12 max-w-3xl rounded-2xl p-0">

                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100">
                                        <i class="fa-solid fa-school text-purple-600"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Periode Khusus Sekolah
                                        </h3>

                                        <p id="tka-tryout-override-period-information" class="mt-0.5 text-sm text-slate-500">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button class="btn btn-sm btn-circle border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-6 py-5">

                            <!-- SUMMARY -->
                            <div class="mb-5 flex flex-col gap-3 border border-blue-100 bg-blue-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p id="tka-tryout-override-total-school" class="text-sm font-semibold text-slate-700">
                                        0 Sekolah
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        Sekolah yang menggunakan periode khusus sebagai pengganti periode default.
                                    </p>
                                </div>

                                <a id="button-add-tka-tryout-override" href="">
                                    <button type="button" class="btn btn-sm w-full rounded-lg border-[#0071BC] bg-[#0071BC] text-white hover:border-[#005A9C] 
                                        hover:bg-[#005A9C] sm:w-auto">

                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Periode Khusus
                                    </button>
                                </a>
                            </div>

                            <!-- SCHOOL LIST -->
                            <div id="container-tka-tryout-override-list" class="max-h-100 overflow-y-auto rounded-xl border border-slate-300">

                                <!-- SKELETON LOADING -->
                                <div id="tka-tryout-override-list-skeleton">
                                    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-40 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="h-7 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                    </div>

                                    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-48 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-32 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="h-7 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                    </div>

                                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-36 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-24 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="h-7 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                    </div>
                                </div>

                                <!-- LIST CONTENT -->
                                <div id="tka-tryout-override-list" class="hidden">
                                    <!-- show data in ajax -->
                                </div>

                                <!-- EMPTY MESSAGE -->
                                <div id="empty-message-tka-tryout-override-list" class="hidden px-6 py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                            <i class="fa-solid fa-school text-xl text-slate-400"></i>
                                        </div>

                                        <p class="mt-4 text-sm font-semibold text-slate-700">
                                            Belum ada periode khusus
                                        </p>

                                        <p class="mt-1 max-w-sm text-xs leading-relaxed text-slate-400">
                                            Belum ada sekolah yang menggunakan periode khusus pada periode ini.
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- MODAL FOOTER -->
                        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                            <form method="dialog">
                                <button class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                    Tutup
                                </button>
                            </form>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- MODAL EDIT PERIODE TRYOUT TKA (NON OVERRIDE) -->
                <dialog id="modal-edit-tka-tryout-period" class="modal">
                    <div class="modal-box w-11/12 max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl bg-white p-0">

                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Edit Periode Tryout TKA
                                    </h2>

                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Perbarui informasi dan pengaturan periode Tryout TKA.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-5 py-6 sm:px-6">

                            <!-- FORM -->
                            <form id="edit-tka-tryout-period-form">

                                <input type="hidden" id="edit-tka-tryout-period-id" name="period_id" value="">

                                <!-- INFORMASI PERIODE -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Informasi Periode
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Perbarui tahun ajaran dan periode pelaksanaan Tryout TKA.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5">

                                        <!-- TAHUN AJARAN -->
                                        <div class="w-full">
                                            <label for="edit-tka-tryout-period-academic-year" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tahun Ajaran
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <select id="academic-year" name="tahun_ajaran" class="w-full h-13 select select-bordered bg-white border border-gray-300 
                                                    rounded-lg py-4 text-sm shadow-sm outline-none transition duration-200 cursor-pointer">

                                                    <option value="" class="hidden">
                                                        Pilih Tahun Ajaran
                                                    </option>

                                                    @foreach ($academicYears as $academicYear)
                                                        <option value="{{ $academicYear }}">
                                                            {{ $academicYear }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <span id="error-tahun_ajaran" class="text-xs font-semibold text-red-500"></span>
                                            </div>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tahun ajaran yang digunakan untuk periode Tryout TKA.
                                            </p>
                                        </div>

                                        <!-- TANGGAL MULAI -->
                                        <div class="w-full">
                                            <label for="edit-tka-tryout-period-start-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="edit-tka-tryout-period-start-date" name="start_date" value="" class="w-full rounded-lg 
                                                    border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" 
                                                    placeholder="Pilih tanggal" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>

                                            <span id="error-edit-start_date" class="text-xs font-semibold text-red-500"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal dimulainya periode Tryout TKA.
                                            </p>
                                        </div>

                                        <!-- TANGGAL SELESAI -->
                                        <div class="w-full">
                                            <label for="edit-tka-tryout-period-end-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="edit-tka-tryout-period-end-date" name="end_date" value="" class="w-full rounded-lg border 
                                                    border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" 
                                                    placeholder="Pilih tanggal" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>

                                            <span id="error-edit-end_date" class="text-xs font-semibold text-red-500"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal berakhirnya periode Tryout TKA.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6 border-t border-slate-200"></div>

                                <!-- PENGATURAN REVIEW -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Pengaturan Review
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Tentukan apakah peserta dapat melihat pembahasan atau hasil review setelah mengerjakan Tryout.
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                        <label for="edit-tka-tryout-period-is-review" class="flex cursor-pointer items-start gap-4">
                                            <input id="edit-tka-tryout-period-is-review" name="is_review" type="checkbox" class="mt-1 h-5 w-5 shrink-0 cursor-pointer 
                                                rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500">

                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-700">
                                                    Aktifkan Review Tryout
                                                </p>

                                                <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                                    Peserta dapat melakukan review terhadap hasil pengerjaan sesuai dengan konfigurasi review yang tersedia.
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <span id="error-edit-is_review" class="text-xs font-semibold text-red-500"></span>
                                </div>

                                <!-- MODAL ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">

                                    <button type="button" id="button-cancel-edit-tka-tryout-period" class="btn w-full rounded-xl border-slate-300 bg-white 
                                        text-slate-600 shadow-sm hover:border-slate-400 hover:bg-slate-50 sm:w-auto">
                                        <i class="fa-solid fa-xmark"></i>
                                        Batal
                                    </button>

                                    <button type="button" id="button-update-tka-tryout-period" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] 
                                        text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">
                            close
                        </button>
                    </form>
                </dialog>

                <!-- MODAL EDIT PERIODE KHUSUS TRYOUT TKA -->
                <dialog id="modal-edit-tka-tryout-period-school-override" class="modal">
                    <div class="modal-box w-11/12 max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl bg-white p-0">

                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Edit Periode Khusus
                                    </h2>

                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Perbarui tanggal pelaksanaan dan pengaturan review untuk periode khusus sekolah.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-5 py-6 sm:px-6">

                            <!-- SCHOOL INFO -->
                            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 shrink-0">
                                        <i class="fa-solid fa-school text-blue-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-blue-800">
                                            Sekolah
                                        </p>

                                        <p id="edit-tka-tryout-override-school-name" class="mt-1 text-xs leading-relaxed text-blue-700 sm:text-sm">
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="edit-tka-tryout-period-school-override-form">
                                <input type="hidden" id="edit-tka-tryout-period-override-period-id" name="period_id">
                                <input type="hidden" id="edit-tka-tryout-period-override-id" name="override_id">

                                <!-- PERIODE -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Periode
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Perbarui kapan periode Tryout TKA mulai dan berakhir.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5">

                                        <!-- TANGGAL MULAI -->
                                        <div class="w-full">
                                            <label for="edit-start-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="edit-start-date" name="start_date" class="w-full bg-white border border-gray-300 rounded-lg px-3 
                                                    py-4 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih Tanggal">

                                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>

                                            <span id="error-edit-start_date" class="text-red-500 text-xs font-semibold"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal mulai periode khusus Tryout TKA untuk sekolah yang dipilih.
                                            </p>
                                        </div>

                                        <!-- TANGGAL SELESAI -->
                                        <div class="w-full">
                                            <label for="edit-end-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="edit-end-date" name="end_date" class="w-full bg-white border border-gray-300 rounded-lg px-3 
                                                    py-4 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih Tanggal">

                                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>

                                            <span id="error-edit-end_date" class="text-red-500 text-xs font-semibold"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal berakhirnya periode khusus Tryout TKA untuk sekolah yang dipilih.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6 border-t border-slate-200"></div>

                                <!-- PENGATURAN REVIEW -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Pengaturan Review
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Tentukan apakah peserta dapat melihat pembahasan atau hasil review setelah mengerjakan Tryout.
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                        <label for="edit-is-review" class="flex cursor-pointer items-start gap-4">
                                            <input id="edit-is-review" name="is_review" type="checkbox" class="mt-1 h-5 w-5 shrink-0 cursor-pointer rounded 
                                                border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500">

                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-700">
                                                    Aktifkan Review Tryout
                                                </p>

                                                <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                                    Peserta dapat melakukan review terhadap hasil pengerjaan sesuai dengan konfigurasi review yang tersedia.
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <span id="error-edit-is_review" class="text-red-500 text-xs font-semibold"></span>
                                </div>

                                <!-- MODAL ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">

                                    <button type="button" id="button-cancel-edit-tka-tryout-period-school-override" class="btn w-full rounded-xl border-slate-300 bg-white 
                                        text-slate-600 shadow-sm hover:border-slate-400 hover:bg-slate-50 sm:w-auto">

                                        <i class="fa-solid fa-xmark"></i>
                                        Batal
                                    </button>

                                    <button type="button" id="button-update-tka-tryout-period-school-override" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] 
                                        text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">

                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- MODAL DETAIL SESI OVERRIDE -->
                <dialog id="modal-tka-tryout-override-session" class="modal">
                    <div class="modal-box w-11/12 max-w-4xl rounded-2xl p-0">

                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100">
                                        <i class="fa-solid fa-clock text-purple-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Daftar Sesi Tryout TKA
                                        </h3>

                                        <p id="tka-tryout-override-session-school-information" class="mt-0.5 text-sm text-slate-500">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="max-h-[65vh] overflow-y-auto px-6 py-5">

                            <!-- INFO -->
                            <div id="tka-tryout-override-session-info" class="mb-6 flex flex-col gap-3 border border-purple-100 bg-purple-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                                <div>
                                    <p id="tka-tryout-override-session-total" class="text-sm font-semibold text-slate-700">
                                        0 Sesi
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        Jadwal sesi Tryout TKA untuk periode khusus sekolah ini.
                                    </p>
                                </div>

                                <a id="button-add-tka-tryout-override-session" href="">
                                    <button type="button" class="btn btn-sm w-full rounded-lg border-[#0071BC] bg-[#0071BC] 
                                        text-white hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Sesi
                                    </button>
                                </a>
                            </div>

                            <!-- SESSION CONTENT -->
                            <div id="tka-tryout-override-session-content">

                                <!-- SKELETON LOADING -->
                                <div id="tka-tryout-override-session-list-skeleton">

                                    <!-- DATE SKELETON 1 -->
                                    <div class="mb-6">

                                        <div class="mb-3 flex items-center gap-2">
                                            <div class="h-9 w-9 shrink-0 animate-pulse rounded-lg bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-4 w-48 animate-pulse rounded bg-slate-200"></div>
                                                <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>

                                        <div class="overflow-hidden rounded-xl border border-slate-200">

                                            <!-- SESSION SKELETON -->
                                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>

                                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>

                                            <div class="flex items-center justify-between gap-4 px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                                    <div class="min-w-0">
                                                        <div class="h-4 w-20 animate-pulse rounded bg-slate-200"></div>
                                                        <div class="mt-2 h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                                                    </div>
                                                </div>

                                                <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SESSION LIST -->
                                <div id="tka-tryout-override-session-list" class="hidden">
                                    <!-- AJAX -->
                                </div>

                                <!-- EMPTY MESSAGE -->
                                <div id="empty-message-tka-tryout-override-session" class="hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-14">
                                    <div class="mx-auto flex max-w-md flex-col items-center text-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-100">
                                            <i class="fa-regular fa-clock text-2xl text-purple-600"></i>
                                        </div>

                                        <h3 class="mt-5 text-base font-semibold text-slate-700">
                                            Belum Ada Sesi Tryout
                                        </h3>

                                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                                            Belum ada sesi yang dijadwalkan untuk periode khusus sekolah ini.
                                            Tambahkan sesi untuk menentukan waktu pelaksanaan Tryout TKA.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FOOTER -->
                        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                            <form method="dialog">
                                <button type="submit" class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                    Tutup
                                </button>
                            </form>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">
                            close
                        </button>
                    </form>
                </dialog>

                <!-- MODAL EDIT SESI TRYOUT TKA -->
                <dialog id="modal-edit-tka-tryout-session" class="modal">
                    <div class="modal-box w-11/12 max-w-4xl rounded-2xl p-0">
                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                        <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Edit Sesi Tryout TKA
                                        </h3>
                                        <p class="mt-0.5 text-sm text-slate-500">
                                            Perbarui jadwal pelaksanaan sesi Tryout TKA.
                                        </p>
                                    </div>
                                </div>
                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-6 py-5">
                            <!-- SESSION INFORMATION -->
                            <div class="mb-6 border border-blue-100 bg-blue-50 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                        <i class="fa-regular fa-calendar text-sm text-blue-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-blue-800">
                                            Informasi Sesi
                                        </p>
                                        <p id="edit-tka-tryout-session-information" class="mt-1 text-sm font-medium text-blue-700">
                                            -
                                        </p>
                                        <p class="mt-1 text-xs leading-relaxed text-blue-600">
                                            Perubahan jadwal akan diterapkan pada sesi Tryout TKA ini.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="edit-tka-tryout-session-form">
                                <input type="hidden" id="edit-tka-tryout-period-id" name="period_id" value="">
                                <input type="hidden" id="edit-session-id" name="session_id" value="">

                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Waktu Pelaksanaan
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Perbarui tanggal dan rentang waktu pelaksanaan sesi Tryout TKA.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5">
                                        <!-- TANGGAL -->
                                        <div class="w-full">
                                            <label for="edit-session-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Sesi
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-session-date" name="session_date" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih tanggal" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-session_date" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal pelaksanaan sesi Tryout TKA.
                                            </p>
                                        </div>

                                        <!-- JAM MULAI -->
                                        <div class="w-full">
                                            <label for="edit-start-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-start-time" name="start_time" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam mulai" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-start_time" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu dimulainya sesi.
                                            </p>
                                        </div>

                                        <!-- JAM SELESAI -->
                                        <div class="w-full">
                                            <label for="edit-end-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-end-time" name="end_time" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam selesai" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-end_time" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu berakhirnya sesi.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FORM ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button" id="button-cancel-edit-tka-tryout-session" class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                        Batal
                                    </button>
                                    <button type="button" id="button-update-tka-tryout-session" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- MODAL BACKDROP -->
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">
                            close
                        </button>
                    </form>
                </dialog>

                <!-- MODAL EDIT SESI TRYOUT TKA OVERRIDE -->
                <dialog id="modal-edit-tka-tryout-override-session" class="modal">
                    <div class="modal-box w-11/12 max-w-4xl rounded-2xl p-0">
                        <!-- MODAL HEADER -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100">
                                        <i class="fa-solid fa-pen-to-square text-purple-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-800">
                                            Edit Sesi Tryout TKA
                                        </h3>
                                        <p class="mt-0.5 text-sm text-slate-500">
                                            Perbarui jadwal sesi Tryout TKA untuk sekolah ini.
                                        </p>
                                    </div>
                                </div>
                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle shrink-0 border-0 bg-slate-100 text-slate-500 hover:bg-slate-200">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- MODAL CONTENT -->
                        <div class="px-6 py-5">
                            <!-- SESSION INFORMATION -->
                            <div class="mb-6 border border-purple-100 bg-purple-50 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-100">
                                        <i class="fa-regular fa-calendar text-sm text-purple-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-purple-800">
                                            Informasi Sesi
                                        </p>
                                        <p id="edit-tka-tryout-override-session-information" class="mt-1 text-sm font-medium text-purple-700">
                                            -
                                        </p>
                                        <p class="mt-1 text-xs leading-relaxed text-purple-600">
                                            Perubahan jadwal akan diterapkan pada sesi Tryout TKA sekolah ini.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="edit-tka-tryout-override-session-form">
                                <input type="hidden" id="edit-tka-tryout-override-period-id" name="period_override_id" value="">
                                <input type="hidden" id="edit-tka-tryout-override-session-id" name="session_id" value="">

                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Waktu Pelaksanaan
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Perbarui tanggal dan rentang waktu pelaksanaan sesi Tryout TKA.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5">
                                        <!-- TANGGAL -->
                                        <div class="w-full">
                                            <label for="edit-override-session-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Sesi
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-override-session-date" name="session_date" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih tanggal" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-override-session_date" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal pelaksanaan sesi Tryout TKA.
                                            </p>
                                        </div>

                                        <!-- JAM MULAI -->
                                        <div class="w-full">
                                            <label for="edit-override-start-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-override-start-time" name="start_time" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam mulai" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-override-start_time" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu dimulainya sesi.
                                            </p>
                                        </div>

                                        <!-- JAM SELESAI -->
                                        <div class="w-full">
                                            <label for="edit-override-end-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="edit-override-end-time" name="end_time" value="" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam selesai" autocomplete="off">
                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-edit-override-end_time" class="text-xs font-semibold text-red-500"></span>
                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu berakhirnya sesi.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FORM ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button" id="button-cancel-edit-tka-tryout-override-session" class="btn w-full rounded-xl border-slate-300 bg-white text-slate-600 hover:bg-slate-100 sm:w-auto">
                                        Batal
                                    </button>
                                    <button type="button" id="button-update-tka-tryout-override-session" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- MODAL BACKDROP -->
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">
                            close
                        </button>
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

<!-- PERIOD MANAGEMENT JS -->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/load-filter.js') }}"></script> <!--- load filter ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/load-kpi.js') }}"></script> <!--- load KPI ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/paginate-tka-tryout-period-management.js') }}"></script> <!--- paginate tryout tka management ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/paginate-tka-tryout-period-school-override.js') }}"></script> <!--- paginate tryout tka period school override ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/tka-tryout-period-edit-form.js') }}"></script> <!--- paginate tryout tka period  edit form ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/tka-tryout-period-school-override-edit-form.js') }}"></script> <!--- paginate tryout tka period school override edit form ---->

<!-- SESSION MANAGEMENT JS -->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-session/paginate-tka-tryout-period-session.js') }}"></script> <!--- paginate tryout tka period session ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-session/tka-tryout-period-edit-session-form.js') }}"></script> <!--- paginate tryout tka edit session form ---->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-session/tka-tryout-period-edit-override-session-form.js') }}"></script> <!--- paginate tryout tka edit override session form ---->

<!-- SUBJECT MANAGEMENT JS -->
<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-subject/paginate-tka-tryout-period-subject.js') }}"></script> <!--- paginate tryout tka period subject ---->