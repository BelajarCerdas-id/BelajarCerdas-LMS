<?php

namespace App\Imports\Syllabus\School;

use App\Events\SyllabusCrud;
use App\Models\Bab;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class SyllabusBabImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
{
    /**
    * @param Collection $collection
    */
    protected $userId;
    protected $schoolName;
    protected $schoolId;
    protected $curriculumId;
    protected $kelasId;
    protected $mapelId;
    protected $sheetTitle = '';
    protected $faseId;

    public function __construct($userId, $schoolName, $schoolId, $curriculumId, $kelasId, $mapelId, $sheetTitle = '', $faseId)
    {
        $this->userId = $userId;
        $this->schoolName = $schoolName;
        $this->schoolId = $schoolId;
        $this->curriculumId = $curriculumId;
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->faseId = $faseId;
        $this->sheetTitle = $sheetTitle;
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
        // Jika sheet kosong → langsung lempar error
        if ($rows->isEmpty() || $rows->every(fn($r) => $r->filter()->isEmpty())) {
            throw ValidationException::withMessages([
                'import' => ["File Excel kosong atau tidak memiliki data valid"]
            ]);
        }

        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 3;

            $validator = Validator::make($row->toArray(), [
                'semester' => 'required',
                'bab' => 'required',
            ], [
                "semester.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Semester wajib diisi.",
                "bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Bab wajib diisi.",
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                continue;
            }

            // 1. Bab
            $bab = Bab::firstOrCreate([
                'nama_bab' => $row['bab'],
                'semester' => $row['semester'],
                'kelas_id' => $this->kelasId,
                'mapel_id' => $this->mapelId,
                'kurikulum_id' => $this->curriculumId,
                'school_partner_id' => $this->schoolId,
                'fase_id' => $this->faseId,
            ], [
                'user_id' => $this->userId,
                'kode' => $row['bab'],
            ]);

            // Broadcast event
            if (isset($bab)) {
                broadcast(new SyllabusCrud('bab', 'import', [$bab]))->toOthers();
            }
        }

        // Handle error
        if (!empty($errors)) {
            throw ValidationException::withMessages(['import' => $errors]);
        }
    }

}
