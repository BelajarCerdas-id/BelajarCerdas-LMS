<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GradeLedgerExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    protected $students;
    protected $subjects;
    protected $schoolName;
    protected $schoolClass;
    protected $semester;
    protected $tahunAjaran;

    public function __construct($students, $subjects, $schoolName, $schoolClass, $semester, $tahunAjaran)
    {
        $this->students = $students;
        $this->subjects = $subjects;
        $this->schoolName = $schoolName;
        $this->schoolClass = $schoolClass;
        $this->semester = $semester;
        $this->tahunAjaran = $tahunAjaran;
    }

    public function title(): string
    {
        return 'Leger Nilai - ' . $this->schoolClass;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->subjects) + 1);

                // GESER TABLE
                $sheet->insertNewRowBefore(1, 3);

                // HITUNG ULANG POSISI
                $headerRow = 4;
                $lastRow = count($this->students) + $headerRow;

                // HEADER ATAS
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");

                $sheet->setCellValue("A1", strtoupper($this->schoolName));
                $sheet->setCellValue("A2", "LEGER NILAI - SEMESTER {$this->semester}");
                $sheet->setCellValue("A3", "TAHUN AJARAN {$this->tahunAjaran}");

                $sheet->getStyle("A1:A3")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // STYLE HEADER TABLE (SETELAH DIGESER)
                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => '4F81BD'],
                        ],
                        'alignment' => [
                            'horizontal' => 'center',
                        ],
                    ]);

                // BORDER TABLE
                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                // CENTER NILAI (kolom B sampai akhir, dari row data)
                $sheet->getStyle("B{$headerRow}:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => 'center',
                            'vertical' => 'center',
                        ],
                    ]);

                // FREEZE
                $sheet->freezePane('A5');
            },
        ];
    }

    public function collection()
    {
        $rows = [];

        // HEADER TABLE (langsung row 1)
        $header = ['Nama Siswa'];
        foreach ($this->subjects as $subject) {
            $header[] = $subject;
        }

        $rows[] = $header;

        // DATA
        foreach ($this->students as $student) {
            $row = [];
            $row[] = $student['name'];

            foreach ($this->subjects as $subject) {
                $row[] = isset($student['subjects'][$subject]) ? (string) $student['subjects'][$subject] : '0';
            }

            $rows[] = $row;
        }

        return new Collection($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}