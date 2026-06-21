<?php

namespace App\Imports\ContractTermStudent;

use App\Models\SchContract;
use App\Models\SchContractTerm;
use App\Models\SchTermStudent;
use App\Models\UserAccount;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class contractStudentImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
{
    protected $userId;
    protected $contractId;
    protected $termId;
    protected $sheetTitle = '';

    public function __construct($userId, $contractId, $termId, $sheetTitle = '')
    {
        $this->userId = $userId;
        $this->contractId = $contractId;
        $this->termId = $termId;
        $this->sheetTitle = $sheetTitle;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty() || $rows->every(fn($r) => $r->filter()->isEmpty())) {
            throw ValidationException::withMessages([
                'import' => ["File Excel kosong atau tidak memiliki data valid"]
            ]);
        }

        $errors = [];

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 3;

            try {

                // VALIDASI
                $validator = Validator::make(
                    $row->toArray(),
                    [
                        'email_akun' => 'required',
                    ],
                    [
                        'email_akun.required' => 'Email akun wajib diisi.',
                    ]
                );

                if ($validator->fails()) {
                    throw new \Exception(
                        $validator->errors()->first()
                    );
                }

                if ($row['role_account'] === 'Siswa') {
                    $user = UserAccount::where('email', $row['email_akun'])->where('role', 'Siswa')->first();
    
                    if (!$user) {
                        throw new \Exception(
                            "Akun siswa {$row['email_akun']} tidak terdaftar."
                        );
                    }

                    $student = SchTermStudent::firstOrCreate(
                        [
                            'term_id' => $this->termId,
                            'student_id' => $user->id,
                            
                        ], [                            
                            'office_id' => $this->userId,
                            'status' => 'active',
                        ],
                    );

                    $schContractTerm = SchContractTerm::findOrFail($this->termId);
                    
                    $schContract = SchContract::where('id', $this->contractId)->first();

                    $contractStudentCount = SchTermStudent::where('term_id', $this->termId)->count();

                    $termAmount = $schContract->price_per_student * $contractStudentCount;

                    $schContractTerm->update([
                        'amount' => $termAmount,
                    ]);
                }

            } catch (\Illuminate\Database\QueryException $e) {

                if ($e->getCode() == 23000) {

                    $errors[] =
                        "Sheet {$this->sheetTitle} - Baris {$rowNumber}: Siswa {$row['email_akun']} sudah terdaftar pada contract term ini.";

                    continue;
                }

                throw $e;
            }
            catch (\Throwable $e) {

                $errors[] =
                    "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'import' => $errors
            ]);
        }
    }
}