<?php

namespace App\Imports\Syllabus;

use App\Events\SyllabusCrud;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SubBab;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class SyllabusImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
{
    /**
    * @param Collection $collection
    */
    protected $userId;
    protected $sheetTitle = '';

    public function __construct($userId, $sheetTitle = '')
    {
        $this->userId = $userId;
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

            // Validasi awal jika ingin diaktifkan kembali
            $validator = Validator::make($row->toArray(), [
                'kurikulum' => 'required',
                'semester' => 'required',
                'fase' => 'required',
                'kelas' => 'required',
                'mata_pelajaran' => 'required',
                'bab' => 'required',
                'sub_bab' => 'required',
            ], [
                "kurikulum.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Kurikulum wajib diisi.",
                "semester.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Semester wajib diisi.",
                "fase.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Fase wajib diisi.",
                "kelas.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Kelas wajib diisi.",
                "mata_pelajaran.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Mata Pelajaran wajib diisi.",
                "bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Bab wajib diisi.",
                "sub_bab.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Sub Bab wajib diisi.",
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                continue;
            }

            // 1. Kurikulum
            $kurikulum = Kurikulum::firstOrCreate([
                'user_id' => $this->userId,
                'nama_kurikulum' => $row['kurikulum'],
            ], [
                'kode' => $row['kurikulum'],
            ]);

            // 2. Fase
            $fase = Fase::firstOrCreate([
                'user_id' => $this->userId,
                'nama_fase' => $row['fase'],
                'kurikulum_id' => $kurikulum->id,
            ], [
                'kode' => $row['fase'],
            ]);

            // 3. Kelas
            $kelas = Kelas::firstOrCreate([
                'user_id' => $this->userId,
                'kelas' => $row['kelas'],
                'fase_id' => $fase->id,
                'kurikulum_id' => $kurikulum->id,
            ], [
                'kode' => $row['kelas'],
            ]);

            // 4. Mapel
            $mapel = Mapel::firstOrCreate([
                'user_id' => $this->userId,
                'mata_pelajaran' => $row['mata_pelajaran'],
                'kelas_id' => $kelas->id,
                'fase_id' => $fase->id,
                'kurikulum_id' => $kurikulum->id,
            ], [
                'kode' => $row['mata_pelajaran'],
            ]);

            // 5. Bab
            $bab = Bab::firstOrCreate([
                'user_id' => $this->userId,
                'nama_bab' => $row['bab'],
                'semester' => $row['semester'],
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'fase_id' => $fase->id,
                'kurikulum_id' => $kurikulum->id,
            ], [
                'kode' => $row['bab'],
            ]);

            // 6. Sub Bab
            $subBab = SubBab::firstOrCreate([
                'user_id' => $this->userId,
                'sub_bab' => $row['sub_bab'],
                'bab_id' => $bab->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'fase_id' => $fase->id,
                'kurikulum_id' => $kurikulum->id,
            ], [
                'kode' => $row['sub_bab'],
            ]);

            // Ambil nama kelas dari relasi Kelas (contoh: "kelas 7") dan samakan formatnya
            $kelasName = strtolower($mapel->Kelas->kelas);

            // Mapping jenjang → daftar kelas yang valid di jenjang tersebut
            $mappingClasses = [
                'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'SMP' => ['kelas 7','kelas 8','kelas 9'],
                'MTS' => ['kelas 7','kelas 8','kelas 9'],
                'SMA' => ['kelas 10','kelas 11','kelas 12'],
                'SMK' => ['kelas 10','kelas 11','kelas 12'],
                'MA'  => ['kelas 10','kelas 11','kelas 12'],
                'MAK' => ['kelas 10','kelas 11','kelas 12'],
            ];

            // Ambil jenjang yang memang memiliki kelas tersebut (misal kelas 7 → SMP, MTS)
            $allowedJenjangs = collect($mappingClasses)->filter(fn ($kelasList) => in_array($kelasName, $kelasList))->keys();
        
            // Ambil semua sekolah dengan jenjang yang sesuai
            $schools = SchoolPartner::whereIn(DB::raw('UPPER(jenjang_sekolah)'), $allowedJenjangs)->get();

            // Hubungkan mapel global ke semua sekolah yang sesuai jenjangnya
            $schools->each(function ($school) use ($mapel) {
                SchoolMapel::firstOrCreate([
                    'mapel_id' => $mapel->id,
                    'school_partner_id' => $school->id,
                ]);
            });

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