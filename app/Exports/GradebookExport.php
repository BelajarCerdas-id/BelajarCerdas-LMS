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

class GradebookExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    // Menyimpan data export dan informasi header excel
    protected $data;
    protected $assessmentTypes;
    protected $schoolName;
    protected $schoolClass;
    protected $semester;
    protected $tahunAjaran;
    protected $subject;
    protected $kkm;

    // Menerima data dari controller
    public function __construct($data, $assessmentTypes, $schoolName, $schoolClass, $semester, $tahunAjaran, $subject, $kkm)
    {
        $this->data = $data;
        $this->assessmentTypes = $assessmentTypes;
        $this->schoolName = $schoolName;
        $this->schoolClass = $schoolClass;
        $this->semester = $semester;
        $this->tahunAjaran = $tahunAjaran;
        $this->subject = $subject;
        $this->kkm = $kkm;
    }

    // Nama sheet excel
    public function title(): string
    {
        return 'Buku Nilai - ' . $this->schoolClass;
    }

    // Menghitung jumlah assessment terbanyak per tipe
    private function getMaxAssessmentCount($typeId)
    {
        $max = 0;

        foreach ($this->data as $student) {
            foreach ($student['types'] as $type) {
                if ($type['type_id'] == $typeId) {
                    $max = max($max, count($type['details']));
                }
            }
        }

        return $max > 0 ? $max : 1;
    }

    // Mengatur style dan merge cell setelah data ditulis ke excel
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // Menambahkan 5 baris kosong di atas untuk header sekolah
                $sheet->insertNewRowBefore(1, 5);

                // Menentukan posisi header dan data
                $headerTop = 6;
                $headerBottom = 7;
                $dataStart = 8;

                $col = 2;

                // Merge header kolom nama siswa
                $sheet->mergeCells("A{$headerTop}:A{$headerBottom}");

                // Merge header tiap tipe assessment
                foreach ($this->assessmentTypes as $type) {

                    $maxDetail = $this->getMaxAssessmentCount($type['id']);

                    $startCol = Coordinate::stringFromColumnIndex($col);
                    $endCol = Coordinate::stringFromColumnIndex($col + $maxDetail);

                    $sheet->mergeCells("{$startCol}{$headerTop}:{$endCol}{$headerTop}");

                    $col += $maxDetail + 1;
                }

                // Menentukan posisi kolom nilai akhir dan kontribusi raport
                $nilaiAkhirCol = $col;
                $col++;

                $kontribusiCol = $col;
                $lastColumn = Coordinate::stringFromColumnIndex($col);

                // Merge kolom nilai akhir
                $sheet->mergeCells(Coordinate::stringFromColumnIndex($nilaiAkhirCol) . "{$headerTop}:" .Coordinate::stringFromColumnIndex($nilaiAkhirCol) . "{$headerBottom}");

                // Merge kolom kontribusi raport
                $sheet->mergeCells(Coordinate::stringFromColumnIndex($kontribusiCol) . "{$headerTop}:" .Coordinate::stringFromColumnIndex($kontribusiCol) . "{$headerBottom}");

                // Menampilkan mata pelajaran
                $sheet->mergeCells("A4:{$lastColumn}4");
                $sheet->setCellValue("A4", "Mata Pelajaran: {$this->subject}");

                // Menampilkan KKM
                $sheet->mergeCells("A5:{$lastColumn}5");
                $sheet->setCellValue("A5", "KKM: {$this->kkm}");

                // Style teks mata pelajaran
                $sheet->getStyle("A4")->applyFromArray([
                    'font' => ['bold' => true],
                    'size' => 11,
                ]);

                // Style teks KKM
                $sheet->getStyle("A5")->applyFromArray([
                    'font' => ['bold' => true],
                    'size' => 11,
                ]);

                // Menentukan baris terakhir data
                $lastRow = count($this->data) + $dataStart - 1;

                // Merge header sekolah
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");

                // Menampilkan nama sekolah, semester, tahun ajaran
                $sheet->setCellValue("A1", strtoupper($this->schoolName));
                $sheet->setCellValue("A2", "BUKU NILAI - SEMESTER {$this->semester}");
                $sheet->setCellValue("A3", "TAHUN AJARAN {$this->tahunAjaran}");

                // Style header sekolah
                $sheet->getStyle("A1:{$lastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                // Style header tabel
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

                // Border semua tabel
                $sheet->getStyle("A{$headerTop}:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                // Membuat semua nilai rata tengah
                $sheet->getStyle("B{$dataStart}:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => 'center',
                            'vertical' => 'center',
                        ],
                    ]);

                // Freeze kolom nama dan header agar tidak ke scroll
                $sheet->freezePane("B{$dataStart}");

                // Memberi warna merah pada nilai kontribusi di bawah KKM
                foreach ($this->data as $index => $item) {

                    $rowIndex = $dataStart + $index;

                    $nilai = (float) $item['final_absolute'];

                    if ($nilai < $this->kkm) {

                        $sheet->getStyle(Coordinate::stringFromColumnIndex($kontribusiCol) . $rowIndex)
                            ->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => 'FF0000'],
                                    'bold' => true
                                ]
                            ]);
                    }
                }
            },
        ];
    }

    // Menyusun data isi excel
    public function collection()
    {
        $rows = [];

        // Header baris 1 dan 2
        $header1 = ['Nama Siswa'];
        $header2 = [''];

        // Membuat header assessment
        foreach ($this->assessmentTypes as $type) {

            $maxDetail = $this->getMaxAssessmentCount($type['id']);

            for ($i = 1; $i <= $maxDetail; $i++) {
                $header1[] = $type['name'];
                $header2[] = $type['name'] . ' ' . $i;
            }

            $header1[] = $type['name'];
            $header2[] = 'Avg';
        }

        // Header nilai akhir dan kontribusi
        $header1[] = 'Nilai Akhir';
        $header2[] = '';

        $header1[] = 'Kontribusi Raport';
        $header2[] = '';

        $rows[] = $header1;
        $rows[] = $header2;

        // Mengisi data siswa
        foreach ($this->data as $item) {

            $row = [$item['name']];

            foreach ($item['types'] as $type) {

                $details = $type['details'];
                $maxDetail = $this->getMaxAssessmentCount($type['type_id']);

                // Isi nilai detail assessment
                foreach ($details as $detail) {
                    $row[] = isset($detail['score']) ? (float) $detail['score'] : 0;
                }

                // Isi 0 jika assessment belum ada
                for ($i = count($details); $i < $maxDetail; $i++) {
                    $row[] = 0;
                }

                // Isi nilai rata-rata per tipe
                $row[] = $type['avg'] ?? 0;
            }

            // Isi nilai akhir dan kontribusi raport
            $row[] = $item['final_normalized'];
            $row[] = $item['final_absolute'];

            $rows[] = $row;
        }

        return new Collection($rows);
    }

    // Style tambahan
    public function styles(Worksheet $sheet)
    {
        return [];
    }
}