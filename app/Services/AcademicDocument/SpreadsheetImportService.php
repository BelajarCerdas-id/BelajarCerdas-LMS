<?php

namespace App\Services\AcademicDocument;

use App\Models\AcademicDocument;
use App\Models\AcademicDocumentCell;
use App\Models\AcademicDocumentSheet;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Throwable;

class SpreadsheetImportService
{
    private static ?array $cellColumnsCache = null;

    private static ?array $sheetColumnsCache = null;

    /**
     * Parse and import a spreadsheet file into AcademicDocumentSheet and AcademicDocumentCell models.
     */
    public function import(string $fullPath, AcademicDocument $document): void
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheetIndex = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $sheetIndex++;

            $sheetData = [
                'academic_document_id' => $document->id,
                'name' => $worksheet->getTitle(),
                'sheet_index' => $sheetIndex,
            ];

            $filteredSheetData = [];
            foreach ($sheetData as $column => $value) {
                if ($this->sheetColumnExists($column)) {
                    $filteredSheetData[$column] = $value;
                }
            }

            $sheet = new AcademicDocumentSheet;
            $sheet->forceFill($filteredSheetData)->save();

            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            // Build merge cells lookup
            $mergedLookup = [];
            foreach ($worksheet->getMergeCells() as $mergeRange) {
                $parts = explode(':', $mergeRange);
                $startCell = $parts[0] ?? null;
                $endCell = $parts[1] ?? $startCell;

                if (! $startCell) {
                    continue;
                }

                [$startColumn, $startRow] = Coordinate::coordinateFromString($startCell);
                [$endColumn, $endRow] = Coordinate::coordinateFromString($endCell);

                $startColumnIndex = Coordinate::columnIndexFromString($startColumn);
                $endColumnIndex = Coordinate::columnIndexFromString($endColumn);

                for ($r = $startRow; $r <= $endRow; $r++) {
                    for ($c = $startColumnIndex; $c <= $endColumnIndex; $c++) {
                        $coordinate = Coordinate::stringFromColumnIndex($c).$r;
                        $mergedLookup[$coordinate] = $mergeRange;
                    }
                }
            }

            // Iterate through cells
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($column = 1; $column <= $highestColumnIndex; $column++) {
                    $cell = $worksheet->getCellByColumnAndRow($column, $row);
                    $value = $cell->getValue();
                    $formula = null;

                    if (is_string($value) && str_starts_with($value, '=')) {
                        $formula = $value;
                    }

                    $calculatedValue = null;
                    if ($formula !== null) {
                        try {
                            $calculatedValue = $cell->getCalculatedValue();
                        } catch (Throwable) {
                        }
                    }

                    $coordinate = $cell->getCoordinate();
                    $isMerged = isset($mergedLookup[$coordinate]);
                    $mergeRange = $mergedLookup[$coordinate] ?? null;
                    $dataType = null;

                    try {
                        $dataType = $cell->getDataType();
                    } catch (Throwable) {
                    }

                    $style = $this->extractCellStyle($cell);
                    $hasStyle = false;

                    try {
                        $hasStyle = $cell->getStyle()->getFill()->getFillType() !== Fill::FILL_NONE;
                    } catch (Throwable) {
                    }

                    if ($value === null && ! $hasStyle && ! $isMerged) {
                        continue;
                    }

                    $cellData = [
                        'academic_document_sheet_id' => $sheet->id,
                        'coordinate' => $coordinate,
                        'row_number' => $row,
                        'column_number' => $column,
                        'column_index' => $column,
                        'value' => $formula !== null ? null : $value,
                        'formula' => $formula,
                        'calculated_value' => $calculatedValue,
                        'data_type' => $dataType,
                        'style' => ! empty($style) ? json_encode($style, JSON_UNESCAPED_UNICODE) : null,
                        'is_merged' => $isMerged,
                        'merge_range' => $mergeRange,
                    ];

                    $filteredCellData = [];
                    foreach ($cellData as $col => $val) {
                        if ($this->cellColumnExists($col)) {
                            $filteredCellData[$col] = $val;
                        }
                    }

                    $academicCell = new AcademicDocumentCell;
                    $academicCell->forceFill($filteredCellData)->save();
                }
            }
        }
    }

    /**
     * Extract cell styles into a structured array representation.
     */
    public function extractCellStyle($cell): array
    {
        try {
            $style = $cell->getStyle();
            $font = $style->getFont();
            $fill = $style->getFill();
            $alignment = $style->getAlignment();
            $border = $style->getBorders();

            return [
                'font' => [
                    'name' => $font->getName(),
                    'size' => $font->getSize(),
                    'bold' => $font->getBold(),
                    'italic' => $font->getItalic(),
                    'underline' => $font->getUnderline(),
                ],
                'fill' => [
                    'type' => $fill->getFillType(),
                    'color' => $fill->getStartColor()->getARGB(),
                ],
                'alignment' => [
                    'horizontal' => $alignment->getHorizontal(),
                    'vertical' => $alignment->getVertical(),
                    'wrap_text' => $alignment->getWrapText(),
                ],
                'border' => [
                    'left' => $border->getLeft()->getBorderStyle(),
                    'right' => $border->getRight()->getBorderStyle(),
                    'top' => $border->getTop()->getBorderStyle(),
                    'bottom' => $border->getBottom()->getBorderStyle(),
                ],
            ];
        } catch (Throwable) {
            return [];
        }
    }

    protected function sheetColumnExists(string $column): bool
    {
        if (self::$sheetColumnsCache === null) {
            try {
                self::$sheetColumnsCache = Schema::getColumnListing('academic_document_sheets');
            } catch (Throwable) {
                return true;
            }
        }

        return in_array($column, self::$sheetColumnsCache, true);
    }

    protected function cellColumnExists(string $column): bool
    {
        if (self::$cellColumnsCache === null) {
            try {
                self::$cellColumnsCache = Schema::getColumnListing('academic_document_cells');
            } catch (Throwable) {
                return true;
            }
        }

        return in_array($column, self::$cellColumnsCache, true);
    }
}
