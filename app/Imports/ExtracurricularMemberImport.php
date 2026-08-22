<?php

namespace App\Imports;

use App\Models\StudentProfile;
use App\Models\ExtracurricularStudent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExtracurricularMemberImport implements ToCollection
{
    protected $schoolId;
    protected $extracurricularId;

    public function __construct($schoolId, $extracurricularId)
    {
        $this->schoolId = $schoolId;
        $this->extracurricularId = $extracurricularId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Skip baris kosong
            if ($row->filter()->count() == 0) {
                continue;
            }

            // Skip header
            if ($index == 0) {
                continue;
            }

            $nisn = trim((string)($row[1] ?? ''));

            if ($nisn == '') {
                continue;
            }

            $student = StudentProfile::with([
                'UserAccount.StudentSchoolClass.SchoolClass.Kelas'
            ])
            ->where('school_partner_id', $this->schoolId)
            ->where('nisn', $nisn)
            ->first();

            if (!$student) {
                continue;
            }

            $class = $student->UserAccount
                ?->StudentSchoolClass
                ->first()
                ?->SchoolClass;

            if (!$class) {
                continue;
            }

            ExtracurricularStudent::firstOrCreate(

                [
                    'extracurricular_id' => $this->extracurricularId,
                    'student_profile_id' => $student->id,
                ],

                [
                    'school_partner_id' => $this->schoolId,

                    'student_name' => $student->nama_lengkap,

                    'kelas' => is_object($class->kelas ?? null)
                        ? ($class->kelas->kelas ?? '')
                        : ($class->kelas ?? ''),

                    'tipe_kelas' => $class->class_name ?? '',

                    'status' => 'active',
                ]
            );
        }
    }
}