<?php

namespace App\Imports;

use App\Models\Extracurricular;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExtracurricularImport implements ToModel, WithHeadingRow
{
    protected $schoolId;

    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function model(array $row)
    {
        return new Extracurricular([

            'school_partner_id' => $this->schoolId,

            'name' => $row['nama_ekstrakurikuler'],

            'description' => $row['deskripsi'],

            'type' => strtolower(trim($row['tipe'])),

            'coach' => $row['pembina'],

            'status' => 'active'

        ]);
    }
}