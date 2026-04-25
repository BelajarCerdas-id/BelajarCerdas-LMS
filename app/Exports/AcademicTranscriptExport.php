<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithEvents, ShouldAutoSize, WithTitle
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AcademicTranscriptExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize, WithTitle
{
    protected $students;
    protected $mapels;
    protected $kkm;
    protected $schoolName;
    protected $schoolClass;
    protected $schoolLogo;
    protected $rankings = [];

    public function __construct($students, $mapels, $kkm, $schoolName = '-', $schoolClass = '-', $schoolLogo = null)
    {
        $this->students = collect($students);
        $this->mapels = $mapels;
        $this->kkm = $kkm;
        $this->schoolName = $schoolName;
        $this->schoolClass = $schoolClass;
        $this->schoolLogo = $schoolLogo;

        $totals = [];

        foreach ($this->students as $index => $student) {
            $grandTotal = 0;

            foreach ($this->mapels as $mapelName => $classData) {
                $scores = [];

                foreach ($classData as $classLevel => $yearData) {
                    foreach ($yearData as $year => $semesterData) {
                        for ($s = 1; $s <= 2; $s++) {
                            $val = $student['mapels'][$mapelName][$classLevel][$year][$s] ?? null;
                            if ($val !== null && $val !== '') {
                                $scores[] = $val;
                            }
                        }
                    }
                }

                $avg = count($scores) ? round(array_sum($scores) / count($scores)) : 0;
                $grandTotal += $avg;
            }

            $totals[$index] = $grandTotal;
        }

        arsort($totals);

        $rank = 1;
        foreach ($totals as $index => $total) {
            $this->rankings[$index] = $rank++;
        }
    }

    public function title(): string
    {
        return 'Transkrip Nilai';
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->students as $index => $student) {
            $row = [$student['name']];
            $grandTotal = 0;

            foreach ($this->mapels as $mapelName => $classData) {

                $scores = [];

                foreach ($classData as $classLevel => $yearData) {
                    foreach ($yearData as $year => $semesterData) {
                        for ($s = 1; $s <= 2; $s++) {
                            $val = $student['mapels'][$mapelName][$classLevel][$year][$s] ?? '';
                            $row[] = $val;

                            if ($val !== '') {
                                $scores[] = $val;
                            }
                        }
                    }
                }

                $avg = count($scores) ? round(array_sum($scores) / count($scores)) : '';
                $row[] = $avg;

                $grandTotal += $avg ?: 0;
            }

            $row[] = $grandTotal;
            $row[] = $this->rankings[$index] ?? '';

            $rows[] = $row;
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        $row1 = ['Nama Siswa'];
        $row2 = [''];
        $row3 = [''];

        foreach ($this->mapels as $mapelName => $classData) {

            $totalCols = 0;

            foreach ($classData as $classLevel => $yearData) {
                foreach ($yearData as $year => $semesterData) {
                    $totalCols += 2;
                }
            }

            $totalCols += 1;

            $row1[] = $mapelName;
            for ($i = 1; $i < $totalCols; $i++) $row1[] = '';

            foreach ($classData as $classLevel => $yearData) {
                foreach ($yearData as $year => $semesterData) {
                    $row2[] = $year;
                    $row2[] = '';

                    $row3[] = "Kelas {$classLevel} - Sem 1";
                    $row3[] = "Kelas {$classLevel} - Sem 2";
                }
            }

            $row2[] = '';
            $row3[] = 'Rata-rata';
        }

        $row1[] = 'TOTAL';
        $row1[] = 'RANK';
        $row2[] = '';
        $row2[] = '';
        $row3[] = '';
        $row3[] = '';

        return [$row1, $row2, $row3];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                $sheet->insertNewRowBefore(1, 4);

                if ($this->schoolLogo && file_exists(public_path($this->schoolLogo))) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('School Logo');
                    $drawing->setDescription('School Logo');
                    $drawing->setPath(public_path($this->schoolLogo));
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet->getDelegate());

                    $sheet->getRowDimension(1)->setRowHeight(45);
                    $sheet->getRowDimension(2)->setRowHeight(25);
                    $sheet->getRowDimension(3)->setRowHeight(25);
                }

                $headerTop = 5;
                $headerMid = 6;
                $headerBottom = 7;
                $dataStart = 8;

                $colIndex = 2;

                foreach ($this->mapels as $mapelName => $classData) {

                    $startCol = $colIndex;

                    foreach ($classData as $classLevel => $yearData) {
                        foreach ($yearData as $year => $semesterData) {
                            $sheet->mergeCellsByColumnAndRow($colIndex, $headerMid, $colIndex + 1, $headerMid);
                            $sheet->setCellValueByColumnAndRow($colIndex, $headerMid, $year);
                            $colIndex += 2;
                        }
                    }

                    $colIndex += 1;
                    $endCol = $colIndex - 1;

                    $sheet->mergeCellsByColumnAndRow($startCol, $headerTop, $endCol, $headerTop);
                }

                $sheet->mergeCells("A{$headerTop}:A{$headerBottom}");

                $totalCol = Coordinate::stringFromColumnIndex($colIndex);
                $rankCol  = Coordinate::stringFromColumnIndex($colIndex + 1);

                $sheet->mergeCells("{$totalCol}{$headerTop}:{$totalCol}{$headerBottom}");
                $sheet->mergeCells("{$rankCol}{$headerTop}:{$rankCol}{$headerBottom}");

                $lastColumn = $rankCol;

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");

                $sheet->setCellValue("A1", strtoupper($this->schoolName));
                $sheet->setCellValue("A2", "TRANSKRIP NILAI");
                $sheet->setCellValue("A3", "KELAS {$this->schoolClass}");

                $sheet->getStyle("A1:{$lastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ],
                ]);

                $sheet->getStyle("A{$headerTop}:{$lastColumn}{$headerBottom}")
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
                            'vertical' => 'center',
                        ],
                    ]);

                $lastRow = $dataStart + count($this->students) - 1;
                $kkmRow = $lastRow + 1;

                $sheet->setCellValue("A{$kkmRow}", "KKM");

                $col = 2;
                foreach ($this->mapels as $mapelName => $classData) {

                    foreach ($classData as $classLevel => $yearData) {
                        foreach ($yearData as $year => $semesterData) {
                            $sheet->setCellValueByColumnAndRow($col, $kkmRow, $this->kkm);
                            $sheet->setCellValueByColumnAndRow($col + 1, $kkmRow, $this->kkm);
                            $col += 2;
                        }
                    }

                    $col += 1; // skip rata-rata
                }

                // STYLE ROW KKM
                $sheet->getStyle("A{$kkmRow}:{$lastColumn}{$kkmRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'D9EAD3'],
                        ],
                        'alignment' => [
                            'horizontal' => 'center',
                            'vertical' => 'center',
                        ],
                    ]);

                // BORDER TABLE
                $sheet->getStyle("A{$headerTop}:{$lastColumn}{$kkmRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                $sheet->getStyle("B{$dataStart}:{$lastColumn}{$kkmRow}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => 'center',
                            'vertical' => 'center',
                        ],
                    ]);

                $sheet->freezePane("B{$dataStart}");
            }
        ];
    }
}