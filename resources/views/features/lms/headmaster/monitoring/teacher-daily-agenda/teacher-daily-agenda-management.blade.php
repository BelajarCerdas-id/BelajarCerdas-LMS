@include('components/sidebar-beranda', ['headerSideNav' => 'Agenda Harian Guru'])

@if (Auth::user()->role === 'Kepala Sekolah')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5 space-y-8">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-create-feedback"></div>

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="space-y-6">
                <!-- HERO -->
                <section class="overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] shadow-xl">
    
                    <div class="flex min-h-67.5 flex-col justify-between p-8 lg:p-10">
    
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
    
                            <!-- LEFT -->
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white">
                                    <i class="fa-solid fa-chart-column"></i>
                                    Dashboard Monitoring
                                </span>
    
                                <h1 class="mt-4 text-2xl font-bold text-white">
                                    Monitoring Agenda Harian Guru
                                </h1>
    
                                <p class="mt-4 max-w-3xl text-sm leading-7 text-blue-100">
                                    Pantau aktivitas mengajar guru, mulai dari
                                    status absensi mata pelajaran hingga Persentase Agenda Guru dalam mengisi
                                    agenda harian pembelajaran.
                                </p>
                            </div>
    
                            <!-- RIGHT -->
                            <div class="inline-flex items-center gap-3 rounded-xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                                    <i class="fa-solid fa-calendar-days text-blue-200"></i>
                                </div>

                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-blue-200">
                                        Monitoring
                                    </p>
    
                                    <p class="text-sm font-semibold text-white">
                                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
    
                        <!-- TAB -->
                        <div class="mt-8">
                            <div
                                class="inline-flex w-full flex-col gap-2 rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur
                                    sm:w-auto sm:flex-row sm:gap-0 sm:p-1">

                                <a href=""
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow
                                        sm:w-auto sm:justify-start">
                                    <i class="fa-solid fa-chart-column"></i>
                                    Dashboard Monitoring
                                </a>

                                <a href="{{ route('lms.headmaster.teacherDailyAgernda.monitoring.history', [
                                    'role' => $role,
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId
                                ]) }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-white transition hover:bg-white/10
                                        sm:w-auto sm:justify-start">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat Agenda Guru
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="kpi-loading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                    @for ($i = 0; $i < 4; $i++)
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm animate-pulse">

                            <div class="flex items-center justify-between">

                                <div class="flex-1">

                                    <!-- Title -->
                                    <div class="h-3 w-32 rounded-full bg-slate-200"></div>

                                    <!-- Value -->
                                    <div class="mt-4 h-9 w-16 rounded-lg bg-slate-300"></div>

                                    <!-- Small text -->
                                    <div class="mt-4 h-2.5 w-24 rounded-full bg-slate-200"></div>

                                </div>

                                <!-- Icon -->
                                <div class="h-14 w-14 rounded-2xl bg-slate-200"></div>

                            </div>

                        </div>
                    @endfor

                </section>
    
                <!-- KPI CARDS -->
                <section id="kpi-content" class="hidden">
                    
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <!-- Total Guru -->
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">Guru Mengajar Hari Ini</p>
                                    <h3 id="total-teaching-teachers" class="mt-2 text-3xl font-bold text-slate-800">0</h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100">
                                    <i class="fa-solid fa-chalkboard-user text-2xl text-blue-600"></i>
                                </div>
                            </div>
                        </div>
        
                        <!-- Sudah Mengisi -->
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">Sudah Mengisi</p>
                                    <h3 id="total-submitted-agenda" class="mt-2 text-3xl font-bold text-green-600">0</h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100">
                                    <i class="fa-solid fa-circle-check text-2xl text-green-600"></i>
                                </div>
                            </div>
                        </div>
        
                        <!-- Belum Mengisi -->
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">Belum Mengisi</p>
                                    <h3 id="total-pending-agenda" class="mt-2 text-3xl font-bold text-red-600">0</h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100">
                                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-600"></i>
                                </div>
                            </div>
                        </div>
        
                        <!-- Persentase Agenda Guru -->
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">Persentase Agenda Guru</p>
                                    <h3 id="completion-rate" class="mt-2 text-3xl font-bold text-slate-800">0%</h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100">
                                    <i class="fa-solid fa-chart-pie text-2xl text-amber-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
    
                <!-- Progress -->
                <section class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

                    <!-- Header -->
                    <div class="mb-5 flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100">
                            <i class="fa-solid fa-chart-pie text-green-600"></i>
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-slate-800">
                                Progress Pengisian Agenda Guru
                            </h3>

                            <p class="text-sm text-slate-500">
                                Ringkasan agenda pembelajaran guru hari ini.
                            </p>
                        </div>

                    </div>

                    <!-- Skeleton -->
                    <div
                        id="teacher-agenda-progress-loading"
                        class="grid grid-cols-1 gap-8 lg:grid-cols-2 animate-pulse">

                        <!-- Skeleton Chart -->
                        <div class="flex justify-center">

                            <div class="relative h-48 w-48">

                                <div
                                    class="h-48 w-48 rounded-full border-18 border-slate-200">
                                </div>

                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center">

                                    <div class="h-8 w-16 rounded bg-slate-200"></div>

                                    <div class="mt-2 h-3 w-14 rounded bg-slate-200"></div>

                                </div>

                            </div>

                        </div>

                        <!-- Skeleton Summary -->
                        <div class="space-y-3">

                            @for ($i = 0; $i < 2; $i++)

                                <div
                                    class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="h-3 w-3 rounded-full bg-slate-200">
                                        </div>

                                        <div>

                                            <div class="h-4 w-28 rounded bg-slate-200"></div>

                                            <div class="mt-2 h-3 w-20 rounded bg-slate-200"></div>

                                        </div>

                                    </div>

                                    <div class="h-6 w-16 rounded bg-slate-200"></div>

                                </div>

                            @endfor

                        </div>

                    </div>

                    <!-- Content -->
                    <div id="teacher-agenda-progress-content" class="hidden">
                        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
    
                            <!-- Chart -->
                            <div class="flex justify-center">
    
                                <div class="relative h-48 w-48">
    
                                    <!-- Background Circle -->
                                    <div
                                        class="absolute inset-0 m-auto h-48 w-48 rounded-full border-18 border-slate-200">
                                    </div>
    
                                    <canvas id="teacherAgendaProgressChart" class="relative z-10"></canvas>
    
                                    <div
                                        class="pointer-events-none absolute inset-0 z-20 flex flex-col items-center justify-center">
    
                                        <span
                                            id="teacher-agenda-progress-percentage"
                                            class="text-3xl font-bold text-slate-800">
                                            0%
                                        </span>
    
                                        <span class="text-[11px] tracking-wide text-slate-500">
                                            Progress
                                        </span>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Summary -->
                            <div class="space-y-3">
    
                                <div
                                    class="flex items-center justify-between rounded-xl border border-green-100 bg-green-50 px-4 py-3">
    
                                    <div class="flex items-center gap-3">
    
                                        <span
                                            class="h-2.5 w-2.5 rounded-full bg-green-500">
                                        </span>
    
                                        <div>
    
                                            <p class="text-sm font-medium text-slate-700">
                                                Sudah Mengisi
                                            </p>
    
                                            <p class="text-xs text-slate-500">
                                                Agenda telah dikirim.
                                            </p>
    
                                        </div>
    
                                    </div>
    
                                    <span
                                        id="teacher-agenda-filled"
                                        class="text-lg font-semibold text-green-600">
    
                                        0 Guru
    
                                    </span>
    
                                </div>
    
                                <div
                                    class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50 px-4 py-3">
    
                                    <div class="flex items-center gap-3">
    
                                        <span
                                            class="h-2.5 w-2.5 rounded-full bg-red-500">
                                        </span>
    
                                        <div>
    
                                            <p class="text-sm font-medium text-slate-700">
                                                Belum Mengisi
                                            </p>
    
                                            <p class="text-xs text-slate-500">
                                                Agenda masih menunggu.
                                            </p>
    
                                        </div>
    
                                    </div>
    
                                    <span
                                        id="teacher-agenda-unfilled"
                                        class="text-lg font-semibold text-red-600">
    
                                        0 Guru
    
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- MONITORING -->
                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <!-- SKELETON LOADING -->
                    <div id="teacher-agenda-list-loading">

                        <!-- Header -->
                        <div class="border-b border-slate-200 px-6 py-5 animate-pulse">

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <!-- Left -->
                                <div class="flex-1 min-w-0">

                                    <div class="h-6 w-60 rounded-lg bg-slate-200"></div>

                                    <div class="mt-3 space-y-2">
                                        <div class="h-4 w-full max-w-xl rounded bg-slate-200"></div>
                                        <div class="h-4 w-3/4 rounded bg-slate-200"></div>
                                    </div>

                                </div>

                                <!-- Right -->
                                <div class="flex self-end gap-3 lg:self-auto">
                                    <div class="h-8 w-32 rounded-full bg-slate-200"></div>
                                    <div class="h-8 w-32 rounded-full bg-slate-200"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 animate-pulse">

                            <div class="mb-5 flex items-center gap-3">

                                <div class="h-11 w-11 rounded-xl bg-slate-200"></div>

                                <div>

                                    <div class="h-5 w-40 rounded bg-slate-200"></div>

                                    <div class="mt-2 h-4 w-72 rounded bg-slate-200"></div>

                                </div>

                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                                @for ($i = 0; $i < 3; $i++)

                                    <div>

                                        <div class="mb-2 h-4 w-20 rounded bg-slate-200"></div>

                                        <div class="h-12 w-full rounded-xl border border-slate-200 bg-white"></div>

                                    </div>

                                @endfor

                            </div>

                        </div>

                        <!-- Card Skeleton -->
                        <div class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-2">

                            @for ($i = 0; $i < 4; $i++)
                                <div class="rounded-2xl border border-slate-200 p-6 animate-pulse">

                                    <!-- Teacher Header -->
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                        <!-- Teacher Info -->
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div class="h-14 w-14 shrink-0 rounded-2xl bg-slate-200"></div>

                                            <div class="flex-1 min-w-0">
                                                <div class="h-5 w-40 max-w-full rounded bg-slate-200"></div>
                                                <div class="mt-2 h-4 w-28 rounded bg-slate-200"></div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        <div class="self-end lg:self-auto">
                                            <div class="h-7 w-28 rounded-full bg-slate-200"></div>
                                        </div>
                                    </div>

                                    <!-- Schedule -->
                                    <div class="mt-5 grid grid-cols-2 gap-4">
                                        @for ($j = 0; $j < 2; $j++)
                                            <div class="rounded-xl bg-slate-50 p-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-4 w-4 rounded bg-slate-200"></div>
                                                    <div class="h-4 w-24 rounded bg-slate-200"></div>
                                                </div>

                                                <div class="mt-3 h-4 w-20 rounded bg-slate-200"></div>
                                            </div>
                                        @endfor
                                    </div>

                                    <!-- Activity -->
                                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-center gap-2 border-b border-slate-200 pb-4">
                                            <div class="h-4 w-4 rounded bg-slate-200"></div>
                                            <div class="h-4 w-48 rounded bg-slate-200"></div>
                                        </div>

                                        <div class="mt-4 space-y-3">
                                            <div class="h-3 w-full rounded bg-slate-200"></div>
                                            <div class="h-3 w-full rounded bg-slate-200"></div>
                                            <div class="h-3 w-3/4 rounded bg-slate-200"></div>
                                        </div>
                                    </div>

                                    <!-- Action -->
                                    <div class="mt-5 flex justify-end">
                                        <div class="h-10 w-36 rounded-xl bg-slate-200"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div id="teacher-agenda-list-content" class="hidden">

                        <!-- Header -->
                        <div class="border-b border-slate-200 px-6 py-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <!-- Left -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xl font-semibold text-slate-800">
                                        Daftar Monitoring Guru
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Monitoring seluruh agenda pembelajaran guru berdasarkan tanggal,
                                        guru, dan status agenda.
                                    </p>
                                </div>

                                <!-- Right -->
                                <div class="flex self-end gap-3 lg:self-auto lg:items-center">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                        Sudah Mengisi
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                        Belum Mengisi
                                    </span>
                                </div>

                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-filter text-blue-600"></i>
                                </div>

                                <div>
                                    <h4 class="text-base font-semibold text-slate-800">
                                        Filter Monitoring
                                    </h4>

                                    <p class="text-sm text-slate-500">
                                        Gunakan filter untuk mempercepat pencarian agenda guru.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                                <!-- Tanggal -->
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Tanggal
                                    </label>

                                    <div class="relative">
                                        <input id="search_date" type="text" value="{{ now()->format('Y-m-d') }}" placeholder="Pilih Tanggal" readonly 
                                            class="h-12 w-full rounded-xl border border-slate-300 bg-white pl-4 pr-12 text-sm text-slate-700 transition-all 
                                            duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guru -->
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Guru Berdasarkan Jadwal
                                    </label>

                                    <div class="relative">

                                        <select id="search_teacher" class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 
                                            text-sm text-slate-700 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none 
                                            cursor-pointer">

                                            <option value="">
                                                Semua Guru
                                            </option>

                                        </select>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                            <i class="fa-solid fa-user-group text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Status Agenda
                                    </label>

                                    <div class="relative">

                                        <select id="search_status" class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 text-sm 
                                            text-slate-700 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

                                            <option value="">
                                                Semua Status
                                            </option>

                                            <option value="submitted">
                                                Sudah Mengisi
                                            </option>

                                            <option value="pending">
                                                Belum Mengisi
                                            </option>
                                        </select>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                            <i class="fa-solid fa-circle-check text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card List -->
                        <div id="grid-list-teacher-daily-agenda-management" class="grid grid-cols-1 gap-5 p-6 xl:grid-cols-2">
                            <!-- Render AJAX -->
                        </div>

                        <!-- Empty State -->
                        <div id="empty-message-teacher-daily-agenda-management" class="hidden px-6 py-16">

                            <div class="mx-auto max-w-md text-center">

                                <div
                                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100">

                                    <i class="fa-solid fa-calendar-xmark text-3xl text-slate-400"></i>

                                </div>

                                <h3 class="mt-6 text-lg font-semibold text-slate-800">
                                    Belum Ada Data Agenda
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Tidak ditemukan guru mengajar yang sesuai dengan filter yang dipilih.
                                    Coba ubah tanggal, guru, atau status agenda untuk melihat data lainnya.
                                </p>
                            </div>
                        </div>

                        <div class="pagination-container-teacher-daily-agenda-management flex justify-center my-10"></div>
                    </div>
                </section>

                <!-- MODAL DETAIL -->
                <dialog id="my_modal_1" class="modal p-4 sm:p-6 lg:p-8">

                    <div class="modal-box max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden rounded-3xl p-0">

                        <!-- Header -->
                        <div class="shrink-0 border-b border-slate-200 bg-white px-8 py-6">

                            <div class="flex items-start justify-between">

                                <div>

                                    <h3 class="text-xl font-bold text-slate-800">
                                        Detail Agenda Harian Guru
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tinjau kegiatan pembelajaran yang telah dilakukan guru kemudian
                                        berikan masukan sebagai bahan evaluasi.
                                    </p>

                                </div>

                                <form method="dialog">
                                    <button class="btn btn-circle btn-ghost">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </form>

                            </div>

                        </div>

                        <!-- Scroll Area -->
                        <div class="flex-1 overflow-y-auto p-8">

                            <div class="space-y-8">

                                <!-- Informasi -->
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Guru
                                        </p>

                                        <p id="detail_teacher_name"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Mata Pelajaran
                                        </p>

                                        <p id="detail_subject"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Kelas
                                        </p>

                                        <p id="detail_class"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200"></div>

                                <!-- KBM -->
                                <div>
                                    <div class="mb-4 flex items-center gap-3">

                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100">
                                            <i class="fa-solid fa-book-open text-blue-600"></i>
                                        </div>

                                        <div>
                                            <h4 class="font-semibold text-slate-800">
                                                Uraian Kegiatan Belajar Mengajar
                                            </h4>

                                            <p class="text-sm text-slate-500">
                                                Aktivitas pembelajaran yang diinput oleh guru.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <div id="detail_learning_activity" class="prose prose-sm max-w-none whitespace-pre-line text-slate-700 leading-7"></div>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200"></div>

                                <!-- Feedback -->
                                <form id="create-feedback-form">

                                    <input type="hidden" id="detail_teacher_agenda_id" name="teacher_daily_agenda_id">

                                    <div>
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100">
                                                <i class="fa-solid fa-comments text-amber-600"></i>
                                            </div>

                                            <div>
                                                <h4 class="font-semibold text-slate-800">
                                                    Feedback
                                                    <sup class="text-red-500">*</sup>
                                                </h4>

                                                <p class="text-sm text-slate-500">
                                                    Berikan apresiasi, saran maupun evaluasi terhadap kegiatan pembelajaran guru.
                                                </p>
                                            </div>
                                        </div>

                                        <textarea id="feedback" name="feedback" rows="6" class="textarea textarea-bordered w-full rounded-2xl 
                                            border-gray-300 resize-none min-h-40 max-h-80 outline-none"
                                            placeholder="Tuliskan feedback di sini..."></textarea>

                                        <span id="error-feedback" class="mt-2 block text-xs font-semibold text-red-500"></span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-8 py-5">

                            <div class="flex items-center justify-end gap-3">

                                <form method="dialog">
                                    <button class="btn btn-ghost rounded-xl">
                                        Tutup
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    id="btn-submit-feedback"
                                    class="btn rounded-xl bg-blue-600 text-white hover:bg-blue-700">

                                    <i class="fa-solid fa-paper-plane"></i>

                                    Simpan Feedback

                                </button>
                            </div>
                        </div>
                    </div>
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

<script src="{{ asset('assets/js/features/lms/headmaster/monitoring/teacher-daily-agenda/management/teacher-daily-agenda-list.js') }}"></script> <!--- teacher daily agenda list ---->
<script src="{{ asset('assets/js/features/lms/headmaster/monitoring/teacher-daily-agenda/management/teacher-daily-agenda-kpi.js') }}"></script> <!--- teacher daily agenda kpi ---->
<script src="{{ asset('assets/js/features/lms/headmaster/monitoring/teacher-daily-agenda/management/teacher-daily-agenda-progress.js') }}"></script> <!--- teacher daily agenda progress ---->