{{--
    Academic Document Hierarchy
    Dipakai oleh: Analisis, PROTA/PROSEM, RPPM, Refleksi Guru.
    Sumber struktur: TeacherMapel -> SchoolClass -> Mapel.
    Tidak membuat SchoolClass baru; "Create Tahun Ajaran" membangun tampilan
    hirarki dari assignment guru yang memang sudah ada.
--}}
@php
    $hierarchyRole = strtolower(trim((string) (Auth::user()?->role ?? '')));
    $hierarchyIsGuru = $hierarchyRole === 'guru';

    $hierarchySchoolId = (int) ($schoolId ?? 0);
    $hierarchyUserId = (int) (Auth::id() ?? 0);

    $selectedTeacherId = (int) request('teacher_id', 0);
    $selectedYear = trim((string) request('tahun_ajaran', ''));
    $selectedClassLevel = trim((string) request('kelas_id', ''));
    $selectedClassId = (int) request('school_class_id', 0);
    $selectedMapelId = (int) request('mapel_id', 0);

    $hierarchyRows = collect();

    if ($hierarchySchoolId > 0) {
        try {
            $hierarchyRows = \App\Models\TeacherMapel::query()
                ->with(['SchoolClass', 'Mapel'])
                ->where('is_active', true)
                ->whereHas('SchoolClass', function ($q) use ($hierarchySchoolId) {
                    $q->where('school_partner_id', $hierarchySchoolId);
                })
                ->get();
        } catch (\Throwable $e) {
            $hierarchyRows = collect();
        }
    }

    if ($hierarchyIsGuru) {
        $hierarchyRows = $hierarchyRows
            ->filter(fn ($row) => (int) ($row->user_id ?? 0) === $hierarchyUserId)
            ->values();
    } elseif ($selectedTeacherId > 0) {
        $hierarchyRows = $hierarchyRows
            ->filter(fn ($row) => (int) ($row->user_id ?? 0) === $selectedTeacherId)
            ->values();
    }

    $hierarchyMapelName = function ($mapel) {
        return $mapel->nama_mapel
            ?? $mapel->mata_pelajaran
            ?? $mapel->nama
            ?? $mapel->name
            ?? ('Mapel #' . ($mapel->id ?? '-'));
    };

    $hierarchyUserNames = collect();
    if (!$hierarchyIsGuru) {
        $ids = $hierarchyRows->pluck('user_id')->filter()->unique()->values();
        if ($ids->isNotEmpty()) {
            try {
                $users = \Illuminate\Support\Facades\DB::table('user_accounts')
                    ->whereIn('id', $ids->all())
                    ->get();

                foreach ($users as $u) {
                    $name = null;
                    foreach (['name', 'nama', 'nama_lengkap', 'full_name', 'display_name', 'username', 'email'] as $column) {
                        if (isset($u->{$column}) && trim((string) $u->{$column}) !== '') {
                            $name = trim((string) $u->{$column});
                            break;
                        }
                    }
                    $hierarchyUserNames[(int) $u->id] = $name ?: ('Guru #' . $u->id);
                }
            } catch (\Throwable $e) {
                // Tidak menggagalkan halaman.
            }
        }
    }

    $hierarchyTeachers = $hierarchyRows
        ->pluck('user_id')
        ->filter()
        ->unique()
        ->mapWithKeys(fn ($id) => [(int) $id => $hierarchyUserNames[(int) $id] ?? ('Guru #' . $id)])
        ->sortBy(fn ($name) => strtolower($name));

    $hierarchyYears = $hierarchyRows
        ->pluck('SchoolClass')
        ->filter()
        ->map(fn ($class) => trim((string) ($class->tahun_ajaran ?? '')))
        ->filter()
        ->unique()
        ->sort()
        ->values();

    if (!$hierarchyIsGuru && $selectedTeacherId <= 0) {
        $hierarchyYears = collect();
    }

    if ($selectedYear !== '') {
        $hierarchyRows = $hierarchyRows->filter(function ($row) use ($selectedYear) {
            return trim((string) ($row->SchoolClass?->tahun_ajaran ?? '')) === $selectedYear;
        })->values();
    }

    $hierarchyClasses = $hierarchyRows
        ->map(function ($row) {
            $class = $row->SchoolClass;
            return [
                'id' => trim((string) ($class?->kelas_id ?? '')),
                'kelas' => trim((string) ($class?->kelas_id ?? '')),
            ];
        })
        ->filter(fn ($item) => $item['id'] !== '' && $item['kelas'] !== '')
        ->unique('id')
        ->sortBy(fn ($item) => strtolower($item['kelas']))
        ->values();

    if ($selectedClassLevel !== '') {
        $hierarchyRows = $hierarchyRows
            ->filter(fn ($row) => trim((string) ($row->SchoolClass?->kelas_id ?? '')) === $selectedClassLevel)
            ->values();
    }

    if ($selectedClassId > 0) {
        $hierarchyRows = $hierarchyRows
            ->filter(fn ($row) => (int) ($row->school_class_id ?? $row->SchoolClass?->id ?? 0) === $selectedClassId)
            ->values();
    }

    $hierarchyTypes = $hierarchyRows
        ->map(function ($row) {
            $class = $row->SchoolClass;
            return [
                'id' => (int) ($class?->id ?? $row->school_class_id ?? 0),
                'name' => trim((string) ($class?->class_name ?? '')),
            ];
        })
        ->filter(fn ($item) => $item['id'] > 0 && $item['name'] !== '')
        ->unique('id')
        ->sortBy(fn ($item) => strtolower($item['name']))
        ->values();

    if ($selectedClassId > 0) {
        $hierarchyRows = $hierarchyRows
            ->filter(fn ($row) => (int) ($row->school_class_id ?? $row->SchoolClass?->id ?? 0) === $selectedClassId)
            ->values();
    }

    $hierarchyMapels = $hierarchyRows
        ->map(function ($row) use ($hierarchyMapelName) {
            return [
                'id' => (int) ($row->mapel_id ?? $row->Mapel?->id ?? 0),
                'name' => $hierarchyMapelName($row->Mapel),
            ];
        })
        ->filter(fn ($item) => $item['id'] > 0)
        ->unique('id')
        ->sortBy(fn ($item) => strtolower($item['name']))
        ->values();

    $hierarchyBase = request()->url();
    $hierarchyUrl = function (array $params = []) use ($hierarchyBase) {
        $current = request()->query();
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($current[$key]);
            } else {
                $current[$key] = $value;
            }
        }
        return $hierarchyBase . (empty($current) ? '' : '?' . http_build_query($current));
    };

    $hierarchyYearUrl = function ($year) use ($hierarchyUrl, $hierarchyIsGuru, $selectedTeacherId) {
        return $hierarchyUrl([
            'teacher_id' => $hierarchyIsGuru ? null : $selectedTeacherId,
            'tahun_ajaran' => $year,
            'kelas_id' => null,
            'school_class_id' => null,
            'mapel_id' => null,
        ]);
    };

    $hierarchyClassUrl = function ($classLevel) use ($hierarchyUrl, $hierarchyIsGuru, $selectedTeacherId, $selectedYear) {
        return $hierarchyUrl([
            'teacher_id' => $hierarchyIsGuru ? null : $selectedTeacherId,
            'tahun_ajaran' => $selectedYear,
            'kelas_id' => $classLevel,
            'school_class_id' => null,
            'mapel_id' => null,
        ]);
    };

    $hierarchyTypeUrl = function ($classId) use ($hierarchyUrl, $hierarchyIsGuru, $selectedTeacherId, $selectedYear, $selectedClassLevel) {
        return $hierarchyUrl([
            'teacher_id' => $hierarchyIsGuru ? null : $selectedTeacherId,
            'tahun_ajaran' => $selectedYear,
            'kelas_id' => $selectedClassLevel,
            'school_class_id' => $classId,
            'mapel_id' => null,
        ]);
    };

    $hierarchyMapelUrl = function ($mapelId) use ($hierarchyUrl, $hierarchyIsGuru, $selectedTeacherId, $selectedYear, $selectedClassLevel, $selectedClassId) {
        return $hierarchyUrl([
            'teacher_id' => $hierarchyIsGuru ? null : $selectedTeacherId,
            'tahun_ajaran' => $selectedYear,
            'kelas_id' => $selectedClassLevel ?: null,
            'school_class_id' => $selectedClassId ?: null,
            'mapel_id' => $mapelId,
        ]);
    };

    $hierarchyResetUrl = $hierarchyUrl([
        'teacher_id' => null,
        'tahun_ajaran' => null,
        'kelas_id' => null,
        'school_class_id' => null,
        'mapel_id' => null,
    ]);
@endphp

<section class="mb-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-[#0071BC]">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-800">Struktur Perangkat Pembelajaran</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $hierarchyIsGuru ? 'Pilih Tahun Ajaran untuk membentuk Kelas → Tipe Kelas → Mapel dari penugasan Anda.' : 'Telusuri Nama → Tahun Ajaran → Kelas → Tipe Kelas → Mapel.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($hierarchyIsGuru)
                <button
                    type="button"
                    id="btnCreateTahunAjaran"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0071BC] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700"
                >
                    <i class="fa-solid fa-plus"></i>
                    Create Tahun Ajaran
                </button>
            @endif

            <a href="{{ $hierarchyResetUrl }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                <i class="fa-solid fa-rotate-left"></i>
                Reset
            </a>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-2 text-xs font-bold">
        @if (!$hierarchyIsGuru)
            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700">Nama</span>
            <span class="text-slate-300">›</span>
        @endif
        <span class="rounded-full {{ $selectedYear !== '' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1.5">Tahun Ajaran</span>
        <span class="text-slate-300">›</span>
        <span class="rounded-full {{ $selectedClassId > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1.5">Kelas</span>
        <span class="text-slate-300">›</span>
        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">Tipe Kelas</span>
        <span class="text-slate-300">›</span>
        <span class="rounded-full {{ $selectedMapelId > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1.5">Mapel</span>
    </div>

    @if (!$hierarchyIsGuru && $selectedTeacherId <= 0)
        <div class="mt-6">
            <div class="mb-3 text-sm font-black text-slate-700">Pilih Nama Guru</div>
            @if ($hierarchyTeachers->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hierarchyTeachers as $teacherId => $teacherName)
                        <a href="{{ $hierarchyUrl(['teacher_id' => $teacherId, 'tahun_ajaran' => null, 'kelas_id' => null, 'school_class_id' => null, 'mapel_id' => null]) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm"><i class="fa-solid fa-user"></i></div>
                                <div class="min-w-0"><div class="truncate text-sm font-bold text-slate-800">{{ $teacherName }}</div><div class="text-xs text-slate-400">Guru</div></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada penugasan Guru aktif pada sekolah ini.</div>
            @endif
        </div>
    @elseif ($selectedYear === '')
        <div class="mt-6">
            <div class="mb-3 text-sm font-black text-slate-700">Pilih Tahun Ajaran</div>
            @if ($hierarchyYears->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hierarchyYears as $year)
                        <a href="{{ $hierarchyYearUrl($year) }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center justify-between"><div><div class="text-xs font-semibold text-slate-400">Tahun Ajaran</div><div class="mt-1 text-lg font-black text-slate-800">{{ $year }}</div></div><i class="fa-solid fa-chevron-right text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500"></i></div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada Tahun Ajaran dari penugasan TeacherMapel aktif.</div>
            @endif
        </div>
    @elseif ($selectedClassLevel === '')
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between"><div class="text-sm font-black text-slate-700">Kelas</div><span class="text-xs text-slate-400">{{ $selectedYear }}</span></div>
            @if ($hierarchyClasses->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hierarchyClasses as $item)
                        <a href="{{ $hierarchyClassUrl($item['id']) }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center justify-between"><div><div class="text-xs font-semibold text-slate-400">Kelas</div><div class="mt-1 text-lg font-black text-slate-800">{{ $item['kelas'] }}</div></div><i class="fa-solid fa-chevron-right text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500"></i></div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Tidak ada Kelas pada Tahun Ajaran {{ $selectedYear }}.</div>
            @endif
        </div>
    @elseif ($selectedClassId <= 0)
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between"><div class="text-sm font-black text-slate-700">Tipe Kelas</div><span class="text-xs text-slate-400">Kelas {{ $selectedClassLevel }}</span></div>
            @if ($hierarchyTypes->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hierarchyTypes as $item)
                        <a href="{{ $hierarchyTypeUrl($item['id']) }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center justify-between"><div><div class="text-xs font-semibold text-slate-400">Tipe Kelas</div><div class="mt-1 text-base font-black text-slate-800">{{ $item['name'] }}</div></div><i class="fa-solid fa-chevron-right text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500"></i></div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Tidak ada Tipe Kelas pada Kelas yang dipilih.</div>
            @endif
        </div>
    @else
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between"><div class="text-sm font-black text-slate-700">Mata Pelajaran</div><span class="text-xs text-slate-400">Pilih mapel untuk membuka daftar dokumen.</span></div>
            @if ($hierarchyMapels->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hierarchyMapels as $item)
                        <a href="{{ $hierarchyMapelUrl($item['id']) }}" class="group rounded-2xl border {{ $selectedMapelId === $item['id'] ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-slate-50' }} p-5 transition hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center justify-between"><div><div class="text-xs font-semibold text-slate-400">Mapel</div><div class="mt-1 text-base font-black text-slate-800">{{ $item['name'] }}</div></div><i class="fa-solid fa-file-lines text-slate-300 transition group-hover:text-blue-500"></i></div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada Mapel aktif pada Tipe Kelas yang dipilih.</div>
            @endif
        </div>
    @endif
</section>

@if ($hierarchyIsGuru)
    <div id="modalCreateTahunAjaran" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div><h3 class="text-lg font-black text-slate-800">Create Tahun Ajaran</h3><p class="mt-1 text-sm text-slate-500">Pilih Tahun Ajaran yang memang dimiliki oleh penugasan Anda.</p></div>
                <button type="button" id="btnCloseTahunAjaran" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="mt-5 grid max-h-72 grid-cols-1 gap-3 overflow-y-auto sm:grid-cols-2">
                @forelse ($hierarchyYears as $year)
                    <a href="{{ $hierarchyYearUrl($year) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-400 hover:bg-blue-50"><div class="text-xs font-semibold text-slate-400">Tahun Ajaran</div><div class="mt-1 font-black text-slate-800">{{ $year }}</div></a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada Tahun Ajaran pada assignment TeacherMapel Anda.</div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modalCreateTahunAjaran');
            const open = document.getElementById('btnCreateTahunAjaran');
            const close = document.getElementById('btnCloseTahunAjaran');
            if (!modal || !open) return;
            open.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
            close?.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
@endif
