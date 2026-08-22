<?php

namespace App\Imports;

use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMeeting;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AttendanceImport implements ToCollection
{
    protected $extracurricularId;

    public function __construct($extracurricularId)
    {
        $this->extracurricularId = $extracurricularId;
    }

    public function collection(Collection $rows)
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil / Buat Meeting dari Header Excel
        |--------------------------------------------------------------------------
        |
        | Excel:
        |
        | A = No
        | B = NISN
        | C = Nama
        | D = Kelas
        | E dst = tanggal pertemuan
        |
        */

        $meetings = [];

        /*
        |--------------------------------------------------------------------------
        | Baris ke-2 Excel = index 1 di Collection
        |--------------------------------------------------------------------------
        */

        $header = $rows->get(1);

        if (!$header) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Mulai dari kolom E
        | E = index 4
        |--------------------------------------------------------------------------
        */

        for ($column = 4; $column < count($header); $column++) {

            $value = $header[$column] ?? null;

            if (trim((string) $value) === '') {
                continue;
            }

            try {

                /*
                |--------------------------------------------------------------------------
                | Kalau Excel menyimpan tanggal sebagai angka
                |--------------------------------------------------------------------------
                */

                if (is_numeric($value)) {

                    $tanggal = Carbon::instance(
                        Date::excelToDateTimeObject($value)
                    )->format('Y-m-d');

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Kalau tanggal berupa string
                    |--------------------------------------------------------------------------
                    */

                    $tanggal = Carbon::parse(
                        trim((string) $value)
                    )->format('Y-m-d');

                }

            } catch (\Exception $e) {

                continue;

            }

            /*
            |--------------------------------------------------------------------------
            | Cari meeting berdasarkan tanggal
            |--------------------------------------------------------------------------
            */

            $meeting = ExtracurricularMeeting::where(
                'extracurricular_id',
                $this->extracurricularId
            )
            ->whereDate('meeting_date', $tanggal)
            ->first();

            /*
            |--------------------------------------------------------------------------
            | Kalau belum ada, buat meeting
            |--------------------------------------------------------------------------
            */

            if (!$meeting) {

                $meetingNumber = (
                    ExtracurricularMeeting::where(
                        'extracurricular_id',
                        $this->extracurricularId
                    )->max('meeting_number') ?? 0
                ) + 1;

                $meeting = ExtracurricularMeeting::create([

                    'extracurricular_id' => $this->extracurricularId,

                    'meeting_number' => $meetingNumber,

                    'meeting_date' => $tanggal,

                    'title' =>
                        $this->getExtracurricularName()
                        . ' - Pertemuan '
                        . $meetingNumber,

                ]);

            }

            $meetings[$column] = $meeting;
        }

        /*
        |--------------------------------------------------------------------------
        | Tidak ada meeting
        |--------------------------------------------------------------------------
        */

        if (empty($meetings)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Import Absensi
        |--------------------------------------------------------------------------
        |
        | Mulai dari baris ke-3 Excel
        | Collection index = 2
        |
        */

        foreach ($rows->slice(2) as $row) {

            /*
            |--------------------------------------------------------------------------
            | Kolom B = NISN
            |--------------------------------------------------------------------------
            */

            $nisn = trim(
                (string) ($row[1] ?? '')
            );

            if ($nisn === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Cari siswa
            |--------------------------------------------------------------------------
            */

            $student = StudentProfile::where(
                'nisn',
                $nisn
            )->first();

            if (!$student) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Loop setiap meeting
            |--------------------------------------------------------------------------
            */

            foreach ($meetings as $column => $meeting) {

                $value = strtoupper(
                    trim(
                        (string) ($row[$column] ?? '')
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | A = hadir
                | kosong = tidak hadir
                |--------------------------------------------------------------------------
                */

                $status = $value === 'A'
                    ? 'present'
                    : 'absent';

                /*
                |--------------------------------------------------------------------------
                | Simpan / update absensi
                |--------------------------------------------------------------------------
                */

                ExtracurricularAttendance::updateOrCreate(

                    [
                        'meeting_id' => $meeting->id,

                        'student_profile_id' => $student->id,
                    ],

                    [
                        'status' => $status,
                    ]

                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Nama Ekstrakurikuler
    |--------------------------------------------------------------------------
    */

    private function getExtracurricularName()
    {
        return \App\Models\Extracurricular::find(
            $this->extracurricularId
        )?->name ?? 'Ekstrakurikuler';
    }
}