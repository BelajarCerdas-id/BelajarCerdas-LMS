<?php

namespace App\Imports\Syllabus\School;

use App\Events\SyllabusCrud;
use App\Models\SubBab;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class SyllabusSubBabImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
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
    protected $babId;
    protected $sheetTitle = '';
    protected $faseId;

    public function __construct($userId, $schoolName, $schoolId, $curriculumId, $kelasId, $mapelId, $babId, $sheetTitle = '', $faseId)
    {
        $this->userId = $userId;
        $this->schoolName = $schoolName;
        $this->schoolId = $schoolId;
        $this->curriculumId = $curriculumId;
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->babId = $babId;
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
                'sub_bab' => 'required',
            ], [
                "sub_bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Sub Bab wajib diisi.",
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                continue;
            }

            // 1. Sub Bab
            $subBab = SubBab::firstOrCreate([
                'sub_bab' => $row['sub_bab'],
                'bab_id' => $this->babId,
                'kelas_id' => $this->kelasId,
                'mapel_id' => $this->mapelId,
                'fase_id' => $this->faseId,
                'kurikulum_id' => $this->curriculumId,
            ], [
                'user_id' => $this->userId,
                'kode' => $row['sub_bab'],
            ]);

            // Broadcast event
            if (isset($subBab)) {
                broadcast(new SyllabusCrud('subBab', 'import', [$subBab]))->toOthers();
            }
        }

        // Handle error
        if (!empty($errors)) {
            throw ValidationException::withMessages(['import' => $errors]);
        }
    }

}
