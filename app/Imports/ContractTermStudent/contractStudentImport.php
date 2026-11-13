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

        // Pass 1: validate every row BEFORE any insert
        $usersByEmail = [];

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 3;

            $validator = Validator::make(
                $row->toArray(),
                [
                    'email_akun' => 'required',
                    'role_account' => 'required|in:Siswa',
                ],
                [
                    'email_akun.required' => 'Email akun wajib diisi.',
                    'role_account.required' => 'Role akun wajib diisi.',
                    'role_account.in' => 'Role akun harus Siswa.',
                ]
            );

            if ($validator->fails()) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: " . $validator->errors()->first();
                continue;
            }

            $user = UserAccount::where('email', $row['email_akun'])->where('role', 'Siswa')->first();

            if (!$user) {
                $errors[] =
                    "Sheet {$this->sheetTitle} - Baris {$rowNumber}: Akun siswa {$row['email_akun']} tidak terdaftar.";
                continue;
            }

            $usersByEmail[$row['email_akun']] = $user;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'import' => $errors
            ]);
        }

        // Pass 2: insert inside one transaction so partial imports cannot happen
        DB::beginTransaction();

        try {

            foreach ($rows as $index => $row) {

                $rowNumber = $index + 3;

                try {

                    $user = $usersByEmail[$row['email_akun']] ?? UserAccount::where('email', $row['email_akun'])->where('role', 'Siswa')->first();

                    if (!$user) {
                        throw new \Exception(
                            "Akun siswa {$row['email_akun']} tidak terdaftar."
                        );
                    }

                    SchTermStudent::firstOrCreate(
                        [
                            'term_id' => $this->termId,
                            'student_id' => $user->id,

                        ], [
                            'office_id' => $this->userId,
                            'status' => 'active',
                        ],
                    );

                } catch (\Illuminate\Database\QueryException $e) {

                    if ($e->getCode() == 23000) {

                        $errors[] =
                            "Sheet {$this->sheetTitle} - Baris {$rowNumber}: Siswa {$row['email_akun']} sudah terdaftar pada contract term ini.";

                        continue;
                    }

                    throw $e;
                }
            }

            // Recompute term amount once from all students on this term (not per row)
            $schContractTerm = SchContractTerm::where('id', $this->termId)->lockForUpdate()->firstOrFail();

            $schContract = SchContract::where('id', $this->contractId)->first();

            $contractStudentCount = SchTermStudent::where('term_id', $this->termId)->count();

            $termAmount = $schContract->price_per_student * $contractStudentCount;

            $schContractTerm->update([
                'amount' => $termAmount,
            ]);

            if (!empty($errors)) {
                throw ValidationException::withMessages([
                    'import' => $errors
                ]);
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}