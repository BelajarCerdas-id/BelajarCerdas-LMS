<?php

namespace App\Imports\ContractTermStudent;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class contractStudentSheetImport implements WithMultipleSheets
{
    protected $userId;
    protected $contractId;
    protected $termId;
    protected $file;

    public function __construct($userId, $contractId, $termId, $file)
    {
        $this->userId = $userId;
        $this->contractId = $contractId;
        $this->termId = $termId;
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
            // Buat instance contractStudentImport untuk tiap sheet. contoh:
            // Sheet dengan nama 'Bulk_Upload_Math' akan di-handle oleh contractStudentImport($userId, 'Bulk_Upload_Math')
            $sheets[$sheetName] = new contractStudentImport($this->userId, $this->contractId, $this->termId, $sheetName);
        }

        return $sheets;
    }
}