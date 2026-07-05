<?php

namespace App\Imports\Syllabus\School;

use App\Imports\Syllabus\School\SchoolSyllabusImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SchoolSyllabusSheetImport implements WithMultipleSheets
{
    protected $userId;
    protected $schoolName;
    protected $schoolId;
    protected $curriculumId;
    protected $faseId;
    protected $file;

    public function __construct($userId, $schoolName, $schoolId, $curriculumId, $file, $faseId)
    {
        $this->userId = $userId;
        $this->schoolName = $schoolName;
        $this->schoolId = $schoolId;
        $this->curriculumId = $curriculumId;
        $this->faseId = $faseId;
        $this->file = $file;
    }

    public function sheets(): array
    {
        // Inisialisasi array kosong untuk menyimpan semua sheet yang akan diimpor
        $sheets = [];

        // Load file Excel (.xlsx) ke dalam objek Spreadsheet
        // $this->file adalah file yang dikirim dari form upload
        // getRealPath() memberikan path file sementara yang bisa dibaca oleh PhpSpreadsheet
        $spreadsheet = IOFactory::load($this->file->getPathName());

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            // Buat instance SchoolSyllabusImport untuk tiap sheet. contoh:
            // Sheet dengan nama 'Bulk_Upload_Math' akan di-handle oleh SchoolSyllabusImport($userId, 'Bulk_Upload_Math')
            $sheets[$sheetName] = new SchoolSyllabusImport($this->userId, $this->schoolName, $this->schoolId, $this->curriculumId, $sheetName, $this->faseId);
        }

        return $sheets;
    }
}
