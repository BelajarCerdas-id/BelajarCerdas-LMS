<?php

namespace App\Imports\Syllabus\School;

use App\Events\SyllabusCrud;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolBab;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SchoolSubBab;
use App\Models\SubBab;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class SchoolSyllabusImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
{
    /**
    * @param Collection $collection
    */
    protected $userId;
    protected $schoolName;
    protected $schoolId;
    protected $curriculumId;
    protected $sheetTitle = '';
    protected $faseId;
    protected $onlyValidate;

    public function __construct($userId, $schoolName, $schoolId, $curriculumId, $sheetTitle = '', $onlyValidate = false, $faseId)
    {
        $this->userId = $userId;
        $this->schoolName = $schoolName;
        $this->schoolId = $schoolId;
        $this->curriculumId = $curriculumId;
        $this->faseId = $faseId;
        $this->sheetTitle = $sheetTitle;
        $this->onlyValidate = $onlyValidate;
    }

    public function title(): string
    {
        return $this->sheetTitle; // set sheet title untuk indetifikasi error pada sheet mana
    }

    public function headingRow(): int
    {
        return 2; // <-- kalo pake WithHeadingRow header row diambil dari kolom pertama, jadi kalo header row tidak di kolom pertama harus di return seperti ini
    }
    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        $this->validateRows($rows);
        
        if (!$this->onlyValidate) {
            $this->importRows($rows);
        }
    }

    private function validateRows(Collection $rows)
    {
        // Jika sheet kosong -> langsung lempar error
        if ($rows->isEmpty() || $rows->every(fn($r) => $r->filter()->isEmpty())) {
            throw ValidationException::withMessages([
                'import' => ["File Excel kosong atau tidak memiliki data valid"]
            ]);
        }

        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 3;

            // Validasi
            $validator = Validator::make($row->toArray(), [
                'kelas' => 'required',
                'mata_pelajaran' => 'required',
                'bab' => 'required',
                'sub_bab' => 'required',
            ], [
                "kelas.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Kelas wajib diisi.",
                "mata_pelajaran.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Mata Pelajaran wajib diisi.",
                "bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Bab wajib diisi.",
                "sub_bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Sub Bab wajib diisi.",
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                continue;
            }

            $school = SchoolPartner::find($this->schoolId);

            // VALIDASI FASE
            $fase = Fase::where('kurikulum_id', $this->curriculumId)->where('nama_fase', $row['fase'])->first();

            if (!$fase) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$row['fase']} tidak ditemukan.";
                continue;
            }

            $allowedPhases = [
                'SD'  => ['Fase A', 'Fase B', 'Fase C'],
                'MI'  => ['Fase A', 'Fase B', 'Fase C'],

                'SMP' => ['Fase D'],
                'MTS' => ['Fase D'],

                'SMA' => ['Fase E', 'Fase F', 'Fase F+'],
                'SMK' => ['Fase E', 'Fase F', 'Fase F+'],
                'MA'  => ['Fase E', 'Fase F', 'Fase F+'],
                'MAK' => ['Fase E', 'Fase F', 'Fase F+'],
            ];

            if (isset($allowedPhases[$school->jenjang_sekolah]) && !in_array($row['fase'], $allowedPhases[$school->jenjang_sekolah])) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$row['fase']} tidak sesuai dengan jenjang {$school->jenjang_sekolah}.";
                continue;
            }

            // VALIDASI KELAS
            $kelas = Kelas::where('kelas', $row['kelas'])->where('fase_id', $fase->id)->where('kurikulum_id', $this->curriculumId)->first();

            if (!$kelas) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$row['kelas']} tidak ditemukan pada fase {$row['fase']}.";
                continue;
            }

            $allowedClass = [
                'SD'  => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
                'MI'  => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],

                'SMP' => ['Kelas 7', 'Kelas 8', 'Kelas 9'],
                'MTS' => ['Kelas 7', 'Kelas 8', 'Kelas 9'],

                'SMA' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                'SMK' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                'MA'  => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                'MAK' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
            ];

            if (isset($allowedClass[$school->jenjang_sekolah]) && !in_array($row['kelas'], $allowedClass[$school->jenjang_sekolah])) {

                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$row['kelas']} tidak sesuai dengan jenjang {$school->jenjang_sekolah}.";
                continue;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'import' => $errors
            ]);
        }
    }

    public function importRows(Collection $rows) 
    {
        foreach ($rows as $index => $row) {

            $fase = Fase::where('kurikulum_id', $this->curriculumId)->where('nama_fase', $row['fase'])->first();

            $kelas = Kelas::where('kelas', $row['kelas'])->where('fase_id', $fase->id)->where('kurikulum_id', $this->curriculumId)->first();

            $faseId = $kelas->fase_id;

            // MAPEL

            // Cari mapel default
            $mapel = Mapel::where('mata_pelajaran', $row['mata_pelajaran'])->where('kelas_id', $kelas->id)->where('kurikulum_id', $this->curriculumId)
            ->when(!is_null($faseId), function ($query) use ($faseId) {
                $query->where('fase_id', $faseId);
            })->whereNull('school_partner_id')->first();

            // Kalau tidak ada cari mapel sekolah
            if (!$mapel) {
                $mapel = Mapel::where('mata_pelajaran', $row['mata_pelajaran'])->where('kelas_id', $kelas->id)->where('kurikulum_id', $this->curriculumId)
                ->when(!is_null($faseId), function ($query) use ($faseId) {
                    $query->where('fase_id', $faseId);
                })->where('school_partner_id', $this->schoolId)->first();
            }

            // Kalau belum ada buat mapel sekolah
            if (!$mapel) {

                $mapel = Mapel::create([
                    'user_id' => $this->userId,
                    'mata_pelajaran' => $row['mata_pelajaran'],
                    'kode' => $row['mata_pelajaran'],
                    'kelas_id' => $kelas->id,
                    'fase_id' => $faseId,
                    'kurikulum_id' => $this->curriculumId,
                    'school_partner_id' => $this->schoolId,
                ]);

            }

            // SCHOOL MAPEL

            SchoolMapel::firstOrCreate([
                'school_partner_id' => $this->schoolId,
                'mapel_id' => $mapel->id,
            ]);

            // BAB

            // Cari bab default
            $bab = Bab::where('nama_bab', $row['bab'])->where('kelas_id', $kelas->id)->where('mapel_id', $mapel->id)->where('kurikulum_id', $this->curriculumId)
            ->when(!is_null($faseId), function ($query) use ($faseId) {
                $query->where('fase_id', $faseId);
            })->when(filled($row['semester'] ?? null),
                function ($query) use ($row) {
                    $query->where('semester', $row['semester']);
                },
                function ($query) {
                    $query->whereNull('semester');
                }
            )->whereNull('school_partner_id')->first();

            // Cari bab sekolah
            if (!$bab) {
                $bab = Bab::where('nama_bab', $row['bab'])->where('kelas_id', $kelas->id)->where('mapel_id', $mapel->id)->where('kurikulum_id', $this->curriculumId)
                ->when(!is_null($faseId), function ($query) use ($faseId) {
                    $query->where('fase_id', $faseId);
                })->when(filled($row['semester'] ?? null),
                    function ($query) use ($row) {
                        $query->where('semester', $row['semester']);
                    },
                    function ($query) {
                        $query->whereNull('semester');
                    }
                )->where('school_partner_id', $this->schoolId)->first();
            }

            // Buat bab sekolah
            if (!$bab) {

                $bab = Bab::create([
                    'user_id' => $this->userId,
                    'nama_bab' => $row['bab'],
                    'kode' => $row['bab'],
                    'semester' => $row['semester'],
                    'kelas_id' => $kelas->id,
                    'mapel_id' => $mapel->id,
                    'fase_id' => $faseId,
                    'kurikulum_id' => $this->curriculumId,
                    'school_partner_id' => $this->schoolId,
                ]);

            }

            SchoolBab::firstOrCreate([
                'school_partner_id' => $this->schoolId,
                'bab_id' => $bab->id,
            ]);

            // SUB BAB

            // Cari sub bab default
            $subBab = SubBab::where('sub_bab', $row['sub_bab'])->where('bab_id', $bab->id)->where('kelas_id', $kelas->id)->where('mapel_id', $mapel->id)
            ->where('kurikulum_id', $this->curriculumId)->when(!is_null($faseId), function ($query) use ($faseId) {
                $query->where('fase_id', $faseId);
            })->whereNull('school_partner_id')->first();

            // Cari sub bab sekolah
            if (!$subBab) {
                $subBab = SubBab::where('sub_bab', $row['sub_bab'])->where('bab_id', $bab->id)->where('kelas_id', $kelas->id)->where('mapel_id', $mapel->id)
                ->where('kurikulum_id', $this->curriculumId)->when(!is_null($faseId), function ($query) use ($faseId) {
                    $query->where('fase_id', $faseId);
                })->where('school_partner_id', $this->schoolId)->first();
            }

            // Buat sub bab sekolah
            if (!$subBab) {

                $subBab = SubBab::create([
                    'user_id' => $this->userId,
                    'sub_bab' => $row['sub_bab'],
                    'kode' => $row['sub_bab'],
                    'bab_id' => $bab->id,
                    'kelas_id' => $kelas->id,
                    'mapel_id' => $mapel->id,
                    'fase_id' => $faseId,
                    'kurikulum_id' => $this->curriculumId,
                    'school_partner_id' => $this->schoolId,
                ]);

            }

            SchoolSubBab::firstOrCreate([
                'school_partner_id' => $this->schoolId,
                'sub_bab_id' => $subBab->id,
            ]);

            // EVENT
            broadcast(new SyllabusCrud('subBab', 'import', [$subBab]))->toOthers();
        }
    }
}