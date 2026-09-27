<?php

namespace App\Http\Controllers;

use App\Models\AcademicDocument;
use App\Models\AcademicDocumentCell;
use App\Models\AcademicDocumentComment;
use App\Models\AcademicDocumentSheet;
use App\Models\Mapel;
use App\Models\TeacherMapel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Throwable;

class AcademicDocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CONSTANTS
    |--------------------------------------------------------------------------
    */

    private const GURU_ROLE = 'guru';

    private const VIEW_ROLES = [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ];

    private const PROTA_PROSEM_TYPE = 'prota_prosem';

    private const BAGAN_ANALISIS_TYPE = 'bagan_analisis';


    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    protected function currentRole(): string
    {
        $user = Auth::user();

        return strtolower(
            trim(
                (string) (
                    $user?->role
                    ?? ''
                )
            )
        );
    }


    protected function isGuru(): bool
    {
        return $this->currentRole()
            === self::GURU_ROLE;
    }


    protected function authorizeRole(): void
    {
        abort_unless(
            in_array(
                $this->currentRole(),
                self::VIEW_ROLES,
                true
            ),
            403,
            'Anda tidak memiliki akses ke fitur ini.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SCHEMA HELPERS
    |--------------------------------------------------------------------------
    */

    protected function documentColumnExists(
        string $column
    ): bool {
        return Schema::hasColumn(
            'academic_documents',
            $column
        );
    }


    protected function sheetColumnExists(
        string $column
    ): bool {
        return Schema::hasColumn(
            'academic_document_sheets',
            $column
        );
    }


    protected function cellColumnExists(
        string $column
    ): bool {
        return Schema::hasColumn(
            'academic_document_cells',
            $column
        );
    }


    protected function commentColumnExists(
        string $column
    ): bool {
        return Schema::hasColumn(
            'academic_document_comments',
            $column
        );
    }


    /**
     * Ambil identitas pengomentar dari user_accounts.
     *
     * Auth aplikasi menggunakan user_accounts, sedangkan tabel
     * academic_document_comments menyimpan user_id. Relasi legacy
     * ke tabel users tidak selalu memiliki nama/jabatan, sehingga
     * identitas komentar harus diambil dari sumber akun aplikasi.
     */
    protected function resolveCommentAuthor(
        ?int $userId = null
    ): array {
        $userId = (int) ($userId ?? Auth::id());

        $name = null;
        $role = null;

        if (
            $userId > 0
            && Schema::hasTable('user_accounts')
            && Schema::hasColumn('user_accounts', 'id')
        ) {
            $account = DB::table('user_accounts')
                ->where('id', $userId)
                ->first();

            if ($account) {
                foreach (
                    [
                        'name',
                        'nama',
                        'nama_lengkap',
                        'full_name',
                        'display_name',
                        'username',
                        'email',
                    ] as $field
                ) {
                    $value = $account->{$field} ?? null;

                    if (
                        is_string($value)
                        && trim($value) !== ''
                    ) {
                        $name = trim($value);
                        break;
                    }
                }

                foreach (
                    [
                        'role',
                        'jabatan',
                        'position',
                    ] as $field
                ) {
                    $value = $account->{$field} ?? null;

                    if (
                        is_string($value)
                        && trim($value) !== ''
                    ) {
                        $role = trim($value);
                        break;
                    }
                }
            }
        }

        $authUser = Auth::user();

        $name =
            $name
            ?? $authUser?->name
            ?? $authUser?->nama
            ?? $authUser?->nama_lengkap
            ?? $authUser?->full_name
            ?? $authUser?->username
            ?? $authUser?->email
            ?? 'Pengguna';

        $role =
            $role
            ?? $authUser?->role
            ?? $authUser?->jabatan
            ?? '';

        return [
            'name' => (string) $name,
            'role' => (string) $role,
        ];
    }


    protected function documentOwnerColumn(): ?string
    {
        foreach (
            [
                'owner_user_id',
                'user_id',
                'created_by',
                'uploaded_by',
            ] as $column
        ) {

            if (
                $this->documentColumnExists(
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | SCHOOL
    |--------------------------------------------------------------------------
    */

    protected function getSchoolProfileOrFail(
        int $schoolId
    ) {
        /*
         * Bila project memiliki SchoolPartner/Profile,
         * bagian ini bisa diarahkan ke model tersebut.
         *
         * Untuk menjaga controller kompatibel,
         * pengecekan utama dilakukan pada document school_partner_id.
         */

        abort_if(
            $schoolId <= 0,
            404,
            'Sekolah tidak ditemukan.'
        );

        return true;
    }


    protected function getSchoolUserIds(
        int $schoolId
    ) {
        /*
         * Mengambil user berdasarkan dokumen yang
         * sudah terikat school_partner_id.
         *
         * Ini sengaja tidak menggunakan role sebagai ID.
         */

        $ownerColumn =
            $this->documentOwnerColumn();

        if (
            !$ownerColumn ||
            !$this->documentColumnExists(
                'school_partner_id'
            )
        ) {
            return collect();
        }

        return AcademicDocument::query()
            ->where(
                'school_partner_id',
                $schoolId
            )
            ->whereNotNull(
                $ownerColumn
            )
            ->pluck(
                $ownerColumn
            )
            ->filter()
            ->unique()
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | TEACHER MAPEL
    |--------------------------------------------------------------------------
    */

    protected function getTeacherMapel(
        int $schoolId
    ) {
        $query =
            TeacherMapel::query()
                ->with([
                    'Mapel',
                    'SchoolClass',
                ])
                ->where(
                    'is_active',
                    true
                );

        if (
            $this->isGuru()
        ) {

            $query->where(
                'user_id',
                Auth::id()
            );
        }

        /*
         * School filtering.
         */

        try {

            $query->whereHas(
                'SchoolClass',
                function ($q) use (
                    $schoolId
                ) {

                    if (
                        Schema::hasColumn(
                            'school_classes',
                            'school_partner_id'
                        )
                    ) {

                        $q->where(
                            'school_partner_id',
                            $schoolId
                        );

                    } elseif (
                        Schema::hasColumn(
                            'school_classes',
                            'school_id'
                        )
                    ) {

                        $q->where(
                            'school_id',
                            $schoolId
                        );
                    }
                }
            );

        } catch (Throwable $e) {
            /*
             * Tetap lanjut bila relasi/schema
             * SchoolClass berbeda pada project.
             */
        }

        return $query->get();
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT QUERIES
    |--------------------------------------------------------------------------
    */

    protected function baganAnalisisDocumentQuery()
    {
        $query =
            AcademicDocument::query();

        if (
            $this->documentColumnExists(
                'document_type'
            )
        ) {

            $query->where(
                'document_type',
                self::BAGAN_ANALISIS_TYPE
            );
        }

        return $query;
    }


    protected function protaProsemDocumentQuery()
    {
        $query =
            AcademicDocument::query();

        if (
            $this->documentColumnExists(
                'document_type'
            )
        ) {

            $query->where(
                'document_type',
                self::PROTA_PROSEM_TYPE
            );
        }

        return $query;
    }


    protected function applyDocumentOwner(
        $query,
        int $userId
    ) {
        $ownerColumn =
            $this->documentOwnerColumn();

        if ($ownerColumn) {

            $query->where(
                $ownerColumn,
                $userId
            );
        }

        return $query;
    }


    protected function getDocumentForSchoolOrFail(
        int $schoolId,
        int $documentId,
        ?string $documentType = null,
        bool $ownerOnly = false
    ) {
        /*
         * PENTING:
         *
         * documentId harus benar-benar ID document.
         *
         * $role TIDAK PERNAH dikirim ke method ini.
         */

        $query =
            AcademicDocument::query()
                ->where(
                    'id',
                    $documentId
                );

        if (
            $this->documentColumnExists(
                'school_partner_id'
            )
        ) {

            $query->where(
                'school_partner_id',
                $schoolId
            );
        }

        if (
            $documentType &&
            $this->documentColumnExists(
                'document_type'
            )
        ) {

            $query->where(
                'document_type',
                $documentType
            );
        }

        if ($ownerOnly) {

            $this->applyDocumentOwner(
                $query,
                (int) Auth::id()
            );
        }

        return $query->firstOrFail();
    }


    /*
    |--------------------------------------------------------------------------
    | MAPEL NAME
    |--------------------------------------------------------------------------
    */

    protected function mapelName(
        $mapel
    ): string {
        if (!$mapel) {
            return 'Mata Pelajaran';
        }

        return $mapel->mata_pelajaran
            ?? $mapel->nama_mapel
            ?? $mapel->nama
            ?? $mapel->name
            ?? 'Mata Pelajaran';
    }


    /*
    |--------------------------------------------------------------------------
    | CELL STYLE
    |--------------------------------------------------------------------------
    */

    protected function extractCellStyle(
        $cell
    ): array {
        try {

            $style =
                $cell->getStyle();

            $font =
                $style->getFont();

            $fill =
                $style->getFill();

            $alignment =
                $style->getAlignment();

            $border =
                $style->getBorders();

            return [

                'font' => [
                    'name' =>
                        $font->getName(),

                    'size' =>
                        $font->getSize(),

                    'bold' =>
                        $font->getBold(),

                    'italic' =>
                        $font->getItalic(),

                    'underline' =>
                        $font->getUnderline(),
                ],

                'fill' => [
                    'type' =>
                        $fill->getFillType(),

                    'color' =>
                        $fill
                            ->getStartColor()
                            ->getARGB(),
                ],

                'alignment' => [
                    'horizontal' =>
                        $alignment->getHorizontal(),

                    'vertical' =>
                        $alignment->getVertical(),

                    'wrap_text' =>
                        $alignment->getWrapText(),
                ],

                'border' => [

                    'left' =>
                        $border
                            ->getLeft()
                            ->getBorderStyle(),

                    'right' =>
                        $border
                            ->getRight()
                            ->getBorderStyle(),

                    'top' =>
                        $border
                            ->getTop()
                            ->getBorderStyle(),

                    'bottom' =>
                        $border
                            ->getBottom()
                            ->getBorderStyle(),
                ],
            ];

        } catch (Throwable $e) {

            return [];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    public function baganAnalisis(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $teacherMapels =
            $this->getTeacherMapel(
                $schoolId
            );

        $mapelGroups =
            $teacherMapels
                ->filter(
                    fn ($teacherMapel) =>
                        !empty(
                            $teacherMapel->mapel_id
                        )
                )
                ->groupBy(
                    'mapel_id'
                );

        $query =
            $this->baganAnalisisDocumentQuery();

        $ownerColumn =
            $this->documentOwnerColumn();

        if (
            $this->isGuru()
        ) {

            if ($ownerColumn) {

                $query->where(
                    $ownerColumn,
                    Auth::id()
                );
            }

        } else {

            $ownerIds =
                $this->getSchoolUserIds(
                    $schoolId
                );

            if (
                $ownerColumn &&
                $ownerIds->isNotEmpty()
            ) {

                $query->whereIn(
                    $ownerColumn,
                    $ownerIds
                );
            }
        }

        $documents =
            $query
                ->latest('id')
                ->get();

        $documentMapelIds =
            $documents
                ->map(
                    fn ($document) =>
                        $document->subject_id
                        ??
                        $document->mapel_id
                        ??
                        null
                )
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $mapelGroups =
            $mapelGroups->filter(
                fn ($items, $mapelId) =>
                    $documentMapelIds
                        ->contains(
                            (int) $mapelId
                        )
            );

        return view(
             'features.lms.school-admin.registrasi-guru.tampilan.tamp-analisis',
            [
                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'teacherMapels' =>
                    $teacherMapels,

                'mapelGroups' =>
                    $mapelGroups,

                'documents' =>
                    $documents,

                'academicDocuments' =>
                    $documents,

                'currentRole' =>
                    $this->currentRole(),

                'role' =>
                    $role,

                'isGuru' =>
                    $this->isGuru(),

                // Halaman /bagan-analisis adalah halaman input utama.
                // Jika Guru sudah punya dokumen, tampilkan dokumen terbaru.
                'document' =>
                    $documents->first(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS UPLOAD
    |--------------------------------------------------------------------------
    */

    public function baganAnalisisUpload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        abort_unless(
            $this->isGuru(),
            403,
            'Hanya guru yang dapat mengupload Bagan Analisis.'
        );

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $teacherMapel =
            $this->getTeacherMapel(
                $schoolId
            )->first();

        abort_unless(
            $teacherMapel,
            422,
            'Guru belum memiliki mata pelajaran aktif.'
        );

        $mapelId =
            $teacherMapel->mapel_id;

        abort_unless(
            $mapelId,
            422,
            'Mata pelajaran guru belum ditemukan.'
        );

        $file =
            $request->file('file');

        $path =
            $file->store(
                'academic-documents/bagan-analisis',
                'public'
            );

        DB::beginTransaction();

        try {

            $documentData = [

                'title' =>
                    $request->input(
                        'title',
                        'Program Tahunan / Program Semester'
                    ),

                'original_filename' =>
                    $file->getClientOriginalName(),

                'file_path' =>
                    $path,

                'school_partner_id' =>
                    $schoolId,

                'owner_user_id' =>
                    Auth::id(),

                'subject_id' =>
                    $mapelId,

                'document_type' =>
                    self::BAGAN_ANALISIS_TYPE,
            ];

            $filteredDocumentData = [];

            foreach (
                $documentData
                as $column => $value
            ) {

                if (
                    $this->documentColumnExists(
                        $column
                    )
                ) {

                    $filteredDocumentData[
                        $column
                    ] = $value;
                }
            }

            $document =
                new AcademicDocument();

            $document
                ->forceFill(
                    $filteredDocumentData
                )
                ->save();

            /*
             * Legacy fields.
             */

            $legacyData = [];

            if (
                $this->documentColumnExists(
                    'file_type'
                )
            ) {

                $legacyData['file_type'] =
                    $file->getClientMimeType();
            }

            if (
                $this->documentColumnExists(
                    'mapel_id'
                )
            ) {

                $legacyData['mapel_id'] =
                    $mapelId;
            }

            if (
                $this->documentColumnExists(
                    'uploaded_by'
                )
            ) {

                $legacyData['uploaded_by'] =
                    Auth::id();
            }

            if (
                $this->documentColumnExists(
                    'created_by'
                )
            ) {

                $legacyData['created_by'] =
                    Auth::id();
            }

            if (
                !empty($legacyData)
            ) {

                $document
                    ->forceFill(
                        $legacyData
                    )
                    ->save();
            }

            $fullPath =
                Storage::disk('public')
                    ->path($path);

            abort_unless(
                file_exists($fullPath),
                500,
                'File Excel tidak ditemukan.'
            );

            $spreadsheet =
                IOFactory::load(
                    $fullPath
                );

            $sheetIndex = 0;

            foreach (
                $spreadsheet
                    ->getWorksheetIterator()
                as $worksheet
            ) {

                $sheetIndex++;

                $sheetData = [

                    'academic_document_id' =>
                        $document->id,

                    'name' =>
                        $worksheet->getTitle(),

                    'sheet_index' =>
                        $sheetIndex,
                ];

                $filteredSheetData = [];

                foreach (
                    $sheetData
                    as $column => $value
                ) {

                    if (
                        $this->sheetColumnExists(
                            $column
                        )
                    ) {

                        $filteredSheetData[
                            $column
                        ] = $value;
                    }
                }

                $sheet =
                    new AcademicDocumentSheet();

                $sheet
                    ->forceFill(
                        $filteredSheetData
                    )
                    ->save();

                $highestRow =
                    $worksheet
                        ->getHighestRow();

                $highestColumn =
                    $worksheet
                        ->getHighestColumn();

                $highestColumnIndex =
                    Coordinate::columnIndexFromString(
                        $highestColumn
                    );

                /*
                 * Merge lookup.
                 */

                $mergedLookup = [];

                foreach (
                    $worksheet
                        ->getMergeCells()
                    as $mergeRange
                ) {

                    $parts =
                        explode(
                            ':',
                            $mergeRange
                        );

                    $startCell =
                        $parts[0] ?? null;

                    $endCell =
                        $parts[1]
                        ?? $startCell;

                    if (
                        !$startCell
                    ) {
                        continue;
                    }

                    [
                        $startColumn,
                        $startRow
                    ] =
                        Coordinate::coordinateFromString(
                            $startCell
                        );

                    [
                        $endColumn,
                        $endRow
                    ] =
                        Coordinate::coordinateFromString(
                            $endCell
                        );

                    $startColumnIndex =
                        Coordinate::columnIndexFromString(
                            $startColumn
                        );

                    $endColumnIndex =
                        Coordinate::columnIndexFromString(
                            $endColumn
                        );

                    for (
                        $r = $startRow;
                        $r <= $endRow;
                        $r++
                    ) {

                        for (
                            $c = $startColumnIndex;
                            $c <= $endColumnIndex;
                            $c++
                        ) {

                            $coordinate =
                                Coordinate::stringFromColumnIndex(
                                    $c
                                ) . $r;

                            $mergedLookup[
                                $coordinate
                            ] =
                                $mergeRange;
                        }
                    }
                }

                /*
                 * Cells.
                 */

                for (
                    $row = 1;
                    $row <= $highestRow;
                    $row++
                ) {

                    for (
                        $column = 1;
                        $column <=
                            $highestColumnIndex;
                        $column++
                    ) {

                        $cell =
                            $worksheet
                                ->getCellByColumnAndRow(
                                    $column,
                                    $row
                                );

                        $value =
                            $cell->getValue();

                        $formula = null;

                        if (
                            is_string($value) &&
                            str_starts_with(
                                $value,
                                '='
                            )
                        ) {

                            $formula =
                                $value;
                        }

                        $calculatedValue =
                            null;

                        if (
                            $formula !== null
                        ) {

                            try {

                                $calculatedValue =
                                    $cell
                                        ->getCalculatedValue();

                            } catch (
                                Throwable $e
                            ) {
                            }
                        }

                        $coordinate =
                            $cell
                                ->getCoordinate();

                        $isMerged =
                            isset(
                                $mergedLookup[
                                    $coordinate
                                ]
                            );

                        $mergeRange =
                            $mergedLookup[
                                $coordinate
                            ]
                            ?? null;

                        $dataType = null;

                        try {

                            $dataType =
                                $cell
                                    ->getDataType();

                        } catch (
                            Throwable $e
                        ) {
                        }

                        $style =
                            $this->extractCellStyle(
                                $cell
                            );

                        $hasStyle = false;

                        try {

                            $hasStyle =
                                $cell
                                    ->getStyle()
                                    ->getFill()
                                    ->getFillType()
                                    !== Fill::FILL_NONE;

                        } catch (
                            Throwable $e
                        ) {
                        }

                        if (
                            $value === null &&
                            !$hasStyle &&
                            !$isMerged
                        ) {
                            continue;
                        }

                        $cellData = [

                            'academic_document_sheet_id' =>
                                $sheet->id,

                            'coordinate' =>
                                $coordinate,

                            'row_number' =>
                                $row,

                            'column_number' =>
                                $column,

                            'column_index' =>
                                $column,

                            'value' =>
                                $formula !== null
                                    ? null
                                    : $value,

                            'formula' =>
                                $formula,

                            'calculated_value' =>
                                $calculatedValue,

                            'data_type' =>
                                $dataType,

                            'style' =>
                                !empty($style)
                                    ? json_encode(
                                        $style,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    )
                                    : null,

                            'is_merged' =>
                                $isMerged,

                            'merge_range' =>
                                $mergeRange,
                        ];

                        foreach (
                            [
                                'column_index',
                                'formula',
                                'calculated_value',
                                'data_type',
                                'style',
                                'is_merged',
                                'merge_range',
                            ]
                            as $optionalColumn
                        ) {

                            if (
                                !$this->cellColumnExists(
                                    $optionalColumn
                                )
                            ) {

                                unset(
                                    $cellData[
                                        $optionalColumn
                                    ]
                                );
                            }
                        }

                        $academicCell =
                            new AcademicDocumentCell();

                        $academicCell
                            ->forceFill(
                                $cellData
                            )
                            ->save();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route(
                    'lms.schoolAdmin.registrasiGuru.baganAnalisis.tampilan',
                    [
                        'role' =>
                            $role,

                        'schoolName' =>
                            $schoolName,

                        'schoolId' =>
                            $schoolId,

                        'mapel_id' =>
                            $mapelId,
                    ]
                )
                ->with(
                    'success',
                    'Bagan Analisis berhasil diupload.'
                );

        } catch (Throwable $e) {

            DB::rollBack();

            if (
                isset($path) &&
                $path &&
                Storage::disk('public')
                    ->exists($path)
            ) {

                Storage::disk('public')
                    ->delete($path);
            }

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS DOWNLOAD
    |--------------------------------------------------------------------------
    */

    public function baganAnalisisDownload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $academicDocument =
            $this->getDocumentForSchoolOrFail(
                $schoolId,
                $document,
                self::BAGAN_ANALISIS_TYPE,
                $this->isGuru()
            );

        abort_unless(
            $academicDocument->file_path,
            404,
            'File dokumen tidak ditemukan.'
        );

        $filePath =
            Storage::disk('public')
                ->path(
                    $academicDocument->file_path
                );

        abort_unless(
            file_exists($filePath),
            404,
            'File dokumen tidak ditemukan di storage.'
        );

        return response()->download(
            $filePath,
            $academicDocument->original_filename
                ?: 'bagan-analisis.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS DELETE
    |--------------------------------------------------------------------------
    */

    public function baganAnalisisDestroy(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        abort_unless(
            $this->isGuru(),
            403,
            'Hanya guru yang dapat menghapus Bagan Analisis.'
        );

        $academicDocument =
            $this->getDocumentForSchoolOrFail(
                $schoolId,
                $document,
                self::BAGAN_ANALISIS_TYPE,
                true
            );

        DB::beginTransaction();

        try {

            $sheetIds =
                AcademicDocumentSheet::query()
                    ->where(
                        'academic_document_id',
                        $academicDocument->id
                    )
                    ->pluck('id');

            if (
                $sheetIds->isNotEmpty()
            ) {

                if (
                    $this->commentColumnExists(
                        'academic_document_sheet_id'
                    )
                ) {

                    AcademicDocumentComment::query()
                        ->whereIn(
                            'academic_document_sheet_id',
                            $sheetIds
                        )
                        ->delete();
                }

                AcademicDocumentCell::query()
                    ->whereIn(
                        'academic_document_sheet_id',
                        $sheetIds
                    )
                    ->delete();
            }

            AcademicDocumentSheet::query()
                ->where(
                    'academic_document_id',
                    $academicDocument->id
                )
                ->delete();

            if (
                $academicDocument->file_path &&
                Storage::disk('public')
                    ->exists(
                        $academicDocument->file_path
                    )
            ) {

                Storage::disk('public')
                    ->delete(
                        $academicDocument->file_path
                    );
            }

            $academicDocument->delete();

            DB::commit();

            return redirect()
                ->route(
                    'lms.schoolAdmin.registrasiGuru.baganAnalisis',
                    [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId,
                    ]
                )
                ->with(
                    'success',
                    'Bagan Analisis berhasil dihapus.'
                );

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS UPDATE CELL
    |--------------------------------------------------------------------------
    */

    public function updateBaganAnalisisCell(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        abort_unless(
            $this->isGuru(),
            403,
            'Hanya guru yang dapat mengubah cell.'
        );

        $academicCell =
            AcademicDocumentCell::query()
                ->with('sheet')
                ->findOrFail($cell);

        $sheet =
            $academicCell->sheet;

        abort_unless(
            $sheet,
            404,
            'Sheet dokumen tidak ditemukan.'
        );

        $documentQuery =
            $this->baganAnalisisDocumentQuery();

        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );

        $document =
            $documentQuery
                ->where(
                    'id',
                    $sheet->academic_document_id
                )
                ->first();

        abort_unless(
            $document,
            404,
            'Dokumen Bagan Analisis tidak ditemukan.'
        );

        $request->validate([
            'value' => [
                'nullable',
            ],
        ]);

        $value =
            $request->input(
                'value'
            );

        if ($value === null) {
            $value = '';
        }

        $data = [
            'value' =>
                $value,
        ];

        /*
         * Bila cell sebelumnya berisi formula, edit manual harus
         * mengubahnya menjadi nilai biasa agar formula lama tidak
         * mengambil alih lagi saat halaman dimuat ulang.
         */
        if (
            $this->cellColumnExists(
                'formula'
            )
        ) {
            $data['formula'] = null;
        }

        if (
            $this->cellColumnExists(
                'calculated_value'
            )
        ) {
            $data['calculated_value'] =
                $value;
        }

        if (
            $this->cellColumnExists(
                'data_type'
            )
        ) {
            $data['data_type'] = 's';
        }

        /*
         * Tulis langsung ke database supaya nilai edit benar-benar
         * tersimpan dan tidak bergantung pada konfigurasi fillable
         * model AcademicDocumentCell.
         */
        $updated =
            DB::table(
                $academicCell->getTable()
            )
                ->where(
                    'id',
                    $academicCell->getKey()
                )
                ->update(
                    $data
                );

        abort_unless(
            $updated > 0 ||
            DB::table(
                $academicCell->getTable()
            )
                ->where(
                    'id',
                    $academicCell->getKey()
                )
                ->exists(),
            404,
            'Cell tidak ditemukan saat menyimpan perubahan.'
        );

        $savedCell =
            AcademicDocumentCell::query()
                ->findOrFail(
                    $academicCell->getKey()
                );

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Cell berhasil diperbarui.',

            'cell' => [
                'id' => $savedCell->id,
                'value' => $savedCell->value,
                'formula' =>
                    $savedCell->formula ?? null,
                'calculated_value' =>
                    $savedCell->calculated_value ?? null,
                'data_type' =>
                    $savedCell->data_type ?? null,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS COMMENT
    |--------------------------------------------------------------------------
    */

public function storeBaganAnalisisComment(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId,
    int $cell
) {
    $this->authorizeRole();
    $this->getSchoolProfileOrFail($schoolId);

    $request->validate([
        'academic_document_cell_id' => [
            'nullable',
            'integer',
            'min:0',
        ],
        'academic_document_sheet_id' => [
            'nullable',
            'integer',
            'exists:academic_document_sheets,id',
        ],
        'academic_document_id' => [
            'nullable',
            'integer',
            'exists:academic_documents,id',
        ],
        'comment_text' => [
            'required',
            'string',
            'max:5000',
        ],
        'selected_text' => [
            'nullable',
            'string',
            'max:10000',
        ],
        'anchor_key' => [
            'nullable',
            'string',
            'max:255',
        ],
        'is_revision' => [
            'nullable',
            'boolean',
        ],
    ]);

    $requestedCellId = (int) (
        $request->input('academic_document_cell_id')
        ?? 0
    );

    /*
     * CELL = 0 berarti komentar umum.
     * Komentar umum tidak membutuhkan cell dan tidak menjadi revisi.
     */
    $isGeneralComment = $requestedCellId <= 0;

    if (!$isGeneralComment) {
        abort_unless(
            $requestedCellId === (int) $cell,
            422,
            'Cell komentar tidak sesuai.'
        );

        abort_unless(
            AcademicDocumentCell::query()
                ->whereKey($requestedCellId)
                ->exists(),
            422,
            'Cell akademik yang dipilih tidak valid.'
        );
    }

    $academicCell = null;
    $sheet = null;

    if (!$isGeneralComment) {
        $academicCell = AcademicDocumentCell::query()
            ->with('sheet')
            ->findOrFail($requestedCellId);

        $sheet = $academicCell->sheet;

        abort_unless(
            $sheet,
            404,
            'Sheet dokumen tidak ditemukan.'
        );

        abort_unless(
            filled($academicCell->coordinate),
            422,
            'Coordinate cell tidak ditemukan.'
        );
    }

    $sheetIdFromRequest = (int) (
        $request->input('academic_document_sheet_id')
        ?? 0
    );

    $documentIdFromRequest = (int) (
        $request->input('academic_document_id')
        ?? 0
    );

    if ($isGeneralComment) {
        /*
         * Untuk komentar umum, sheet dipilih dari form.
         * Jika sheet tidak dikirim, gunakan sheet pertama dari dokumen.
         */
        if ($sheetIdFromRequest > 0) {
            $sheet = AcademicDocumentSheet::query()
                ->whereKey($sheetIdFromRequest)
                ->first();

            abort_unless(
                $sheet,
                404,
                'Sheet dokumen tidak ditemukan.'
            );

            $documentIdFromRequest =
                (int) $sheet->academic_document_id;
        } elseif ($documentIdFromRequest > 0) {
            $sheet = AcademicDocumentSheet::query()
                ->where(
                    'academic_document_id',
                    $documentIdFromRequest
                )
                ->orderBy('sheet_index')
                ->first();

            abort_unless(
                $sheet,
                404,
                'Sheet dokumen tidak ditemukan.'
            );
        } else {
            abort(
                422,
                'Dokumen komentar tidak ditemukan.'
            );
        }
    }

    $documentQuery =
        $this->baganAnalisisDocumentQuery()
            ->where(
                'id',
                (int) $sheet->academic_document_id
            );

    if ($this->isGuru()) {
        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );
    } else {
        $ownerColumn =
            $this->documentOwnerColumn();

        $ownerIds =
            $this->getSchoolUserIds($schoolId);

        if (
            $ownerColumn
            && $ownerIds->isNotEmpty()
        ) {
            $documentQuery->whereIn(
                $ownerColumn,
                $ownerIds
            );
        }
    }

    if (
        $this->documentColumnExists('school_partner_id')
    ) {
        $documentQuery->where(
            'school_partner_id',
            $schoolId
        );
    }

    $document = $documentQuery->first();

    abort_unless(
        $document,
        404,
        'Dokumen Bagan Analisis tidak ditemukan.'
    );

    abort_unless(
        (int) $sheet->academic_document_id
            === (int) $document->id,
        404,
        'Sheet tidak termasuk dokumen ini.'
    );

    if (
        !$isGeneralComment
        && $academicCell
    ) {
        abort_unless(
            (int) $academicCell->sheet?->academic_document_id
                === (int) $document->id,
            404,
            'Cell tidak termasuk dokumen ini.'
        );
    }

    $commentText =
        $request->input('comment_text');

    /*
     * Komentar umum tidak boleh berubah menjadi revisi walaupun
     * checkbox lama masih mengirim nilai tertentu.
     */
    $isRevision =
        !$isGeneralComment
        && $request->boolean('is_revision');

    $commentData = [
        'academic_document_id' =>
            $document->id,

        'academic_document_sheet_id' =>
            $sheet->id,

        'academic_document_cell_id' =>
            $isGeneralComment
                ? null
                : $academicCell?->id,

        'coordinate' =>
            $isGeneralComment
                ? '__GENERAL__'
                : $academicCell?->coordinate,

        'user_id' =>
            Auth::id(),

        'comment' =>
            $commentText,

        'comment_text' =>
            $commentText,

        'selected_text' =>
            $isGeneralComment
                ? null
                : $request->input('selected_text'),

        'anchor_key' =>
            $isGeneralComment
                ? null
                : $request->input('anchor_key'),

        'is_revision' =>
            $isRevision,

        'is_resolved' =>
            false,
    ];

    $filteredData = [];

    foreach (
        $commentData
        as $column => $value
    ) {
        if (
            $this->commentColumnExists(
                $column
            )
        ) {
            $filteredData[$column] =
                $value;
        }
    }

    /*
     * Kolom inti tetap dipastikan ada bila schema memang memilikinya.
     */
    foreach (
        [
            'academic_document_id' =>
                $document->id,

            'academic_document_sheet_id' =>
                $sheet->id,

            'academic_document_cell_id' =>
                $isGeneralComment
                    ? null
                    : $academicCell?->id,

            'coordinate' =>
                $isGeneralComment
                    ? '__GENERAL__'
                    : $academicCell?->coordinate,

            'comment' =>
                $commentText,
        ] as $column => $value
    ) {
        if (
            $this->commentColumnExists(
                $column
            )
        ) {
            $filteredData[$column] =
                $value;
        }
    }

    $comment =
        new AcademicDocumentComment();

    $comment->forceFill(
        $filteredData
    );

    $comment->save();

    if (
        $isRevision
        && $academicCell
        && $this->cellColumnExists(
            'has_revision'
        )
    ) {
        $academicCell
            ->forceFill([
                'has_revision' => true,
            ])
            ->save();
    }

    $author =
        $this->resolveCommentAuthor(
            (int) Auth::id()
        );

    try {
        $comment->load('user');
    } catch (Throwable $e) {
        // Relasi legacy user tidak boleh menggagalkan komentar.
    }

    return response()->json([
        'success' => true,

        'message' =>
            $isRevision
                ? 'Komentar revisi berhasil ditambahkan.'
                : 'Komentar berhasil ditambahkan.',

        'comment_id' =>
            $comment->id,

        'document_id' =>
            $document->id,

        'sheet_id' =>
            $sheet->id,

        'cell_id' =>
            $isGeneralComment
                ? null
                : $academicCell?->id,

        'coordinate' =>
            $isGeneralComment
                ? '__GENERAL__'
                : $academicCell?->coordinate,

        'is_general_comment' =>
            $isGeneralComment,

        'is_revision' =>
            $isRevision,

        'user_id' =>
            Auth::id(),

        'user_name' =>
            $author['name'],

        'role' =>
            $author['role'],
    ]);
}

    public function analisis(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $teacherMapels =
            $this->getTeacherMapel(
                $schoolId
            );

        $mapelGroups =
            $teacherMapels
                ->filter(
                    fn ($teacherMapel) =>
                        !empty(
                            $teacherMapel->mapel_id
                        )
                )
                ->groupBy(
                    'mapel_id'
                );

        $query =
            $this->baganAnalisisDocumentQuery();

        $ownerColumn =
            $this->documentOwnerColumn();

        if (
            $this->isGuru()
        ) {

            if ($ownerColumn) {

                $query->where(
                    $ownerColumn,
                    Auth::id()
                );
            }

        } else {

            $ownerIds =
                $this->getSchoolUserIds(
                    $schoolId
                );

            if (
                $ownerColumn &&
                $ownerIds->isNotEmpty()
            ) {

                $query->whereIn(
                    $ownerColumn,
                    $ownerIds
                );
            }
        }

        $documents =
            $query
                ->latest('id')
                ->get();

        $documentMapelIds =
            $documents
                ->map(
                    fn ($document) =>
                        $document->subject_id
                        ??
                        $document->mapel_id
                        ??
                        null
                )
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $mapelGroups =
            $mapelGroups->filter(
                fn ($items, $mapelId) =>
                    $documentMapelIds
                        ->contains(
                            (int) $mapelId
                        )
            );

        return view(
            'features.lms.school-admin.registrasi-guru.analisis',
            [
                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'teacherMapels' =>
                    $teacherMapels,

                'mapelGroups' =>
                    $mapelGroups,

                'documents' =>
                    $documents,

                'academicDocuments' =>
                    $documents,

                'currentRole' =>
                    $this->currentRole(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TAMPILAN ANALISIS
    |--------------------------------------------------------------------------
    */

    public function tampilanAnalisis(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $mapelId =
            $request->integer(
                'mapel_id'
            );

        if (!$mapelId) {

            $mapelId =
                $request->integer(
                    'mapel'
                );
        }

        abort_if(
            !$mapelId,
            404,
            'Mata pelajaran belum dipilih.'
        );

        $mapel =
            Mapel::findOrFail(
                $mapelId
            );

        $teacherMapels =
            TeacherMapel::query()
                ->with([
                    'Mapel',
                    'SchoolClass',
                ])
                ->where(
                    'mapel_id',
                    $mapelId
                )
                ->where(
                    'is_active',
                    true
                );

        if (
            $this->isGuru()
        ) {

            $teacherMapels->where(
                'user_id',
                Auth::id()
            );
        }

        try {

            $teacherMapels->whereHas(
                'SchoolClass',
                function ($query) use (
                    $schoolId
                ) {

                    if (
                        Schema::hasColumn(
                            'school_classes',
                            'school_partner_id'
                        )
                    ) {

                        $query->where(
                            'school_partner_id',
                            $schoolId
                        );

                    } elseif (
                        Schema::hasColumn(
                            'school_classes',
                            'school_id'
                        )
                    ) {

                        $query->where(
                            'school_id',
                            $schoolId
                        );
                    }
                }
            );

        } catch (Throwable $e) {
        }

        $teacherMapels =
            $teacherMapels->get();

        $jumlahGuruMapel =
            $teacherMapels
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->count();

        $documentQuery =
            $this->baganAnalisisDocumentQuery();

        if (
            $this->isGuru()
        ) {

            $this->applyDocumentOwner(
                $documentQuery,
                (int) Auth::id()
            );

        } else {

            $ownerColumn =
                $this->documentOwnerColumn();

            $ownerIds =
                $this->getSchoolUserIds(
                    $schoolId
                );

            if (
                $ownerColumn &&
                $ownerIds->isNotEmpty()
            ) {

                $documentQuery->whereIn(
                    $ownerColumn,
                    $ownerIds
                );
            }
        }

        if (
            $this->documentColumnExists(
                'subject_id'
            )
        ) {

            $documentQuery->where(
                'subject_id',
                $mapelId
            );

        } elseif (
            $this->documentColumnExists(
                'mapel_id'
            )
        ) {

            $documentQuery->where(
                'mapel_id',
                $mapelId
            );
        }

        $document =
            $documentQuery
                ->with([
                    'sheets' => function ($query) {

                        $query->orderBy(
                            'sheet_index'
                        );
                    },
                ])
                ->latest('id')
                ->first();

        $comments =
            collect();

        if ($document) {

            $sheetIds =
                $document
                    ->sheets
                    ->pluck('id');

            if (
                $sheetIds->isNotEmpty() &&
                $this->commentColumnExists(
                    'academic_document_sheet_id'
                )
            ) {

                $comments =
                    AcademicDocumentComment::query()
                        ->with([
                            'user',
                        ])
                        ->whereIn(
                            'academic_document_sheet_id',
                            $sheetIds
                        )
                        ->latest('id')
                        ->get();
            }
        }

        return view(
            'features.lms.school-admin.registrasi-guru.tampilan.tamp-analisis',
            [
                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'mapel' =>
                    $mapel,

                'mapelId' =>
                    $mapelId,

                'mapelName' =>
                    $this->mapelName(
                        $mapel
                    ),

                'teacherMapels' =>
                    $teacherMapels,

                'jumlahGuruMapel' =>
                    $jumlahGuruMapel,

                'currentRole' =>
                    $this->currentRole(),

                'document' =>
                    $document,

                'documents' =>
                    $document
                        ? collect([
                            $document,
                        ])
                        : collect(),

                'academicDocuments' =>
                    $document
                        ? collect([
                            $document,
                        ])
                        : collect(),

                'comments' =>
                    $comments,

                'isGuru' =>
                    $this->isGuru(),

                'uploadRoute' =>
                    route(
                        'lms.schoolAdmin.registrasiGuru.analisis.upload',
                        [
                            'role' =>
                                $role,

                            'schoolName' =>
                                $schoolName,

                            'schoolId' =>
                                $schoolId,
                        ]
                    ),

                'downloadRoute' =>
                    $document
                        ? route(
                            'lms.schoolAdmin.registrasiGuru.analisis.download',
                            [
                                'role' =>
                                    $role,

                                'schoolName' =>
                                    $schoolName,

                                'schoolId' =>
                                    $schoolId,

                                'document' =>
                                    $document->id,
                            ]
                        )
                        : '#',

                'deleteRoute' =>
                    $document
                        ? route(
                            'lms.schoolAdmin.registrasiGuru.analisis.delete',
                            [
                                'role' =>
                                    $role,

                                'schoolName' =>
                                    $schoolName,

                                'schoolId' =>
                                    $schoolId,

                                'document' =>
                                    $document->id,
                            ]
                        )
                        : '#',

                'updateCellRoute' =>
                    route(
                        'lms.schoolAdmin.registrasiGuru.analisis.cells.update',
                        [
                            'role' =>
                                $role,

                            'schoolName' =>
                                $schoolName,

                            'schoolId' =>
                                $schoolId,

                            'cell' =>
                                '__CELL__',
                        ]
                    ),

                'mapels' =>
                    $this->isGuru()
                        ? collect()
                        : Mapel::query()->get(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS WRAPPERS
    |--------------------------------------------------------------------------
    */

    public function analisisUpload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        return $this->baganAnalisisUpload(
            $request,
            $role,
            $schoolName,
            $schoolId
        );
    }


    public function analisisDownload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        return $this->baganAnalisisDownload(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $document
        );
    }


    public function analisisDestroy(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        return $this->baganAnalisisDestroy(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $document
        );
    }


    public function updateAnalisisCell(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ) {
        return $this->updateBaganAnalisisCell(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $cell
        );
    }


    public function storeAnalisisComment(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ) {
        return $this->storeBaganAnalisisComment(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $cell
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM COMMENT
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM MENU
    |--------------------------------------------------------------------------
    */

public function protaProsem(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId
) {
    $this->authorizeRole();

    $this->getSchoolProfileOrFail(
        $schoolId
    );

    /*
    |--------------------------------------------------------------------------
    | DATA GURU & MATA PELAJARAN
    |--------------------------------------------------------------------------
    */

    $teacherMapels = $this->getTeacherMapel(
        $schoolId
    );

    $mapelGroups = $teacherMapels
        ->filter(
            fn ($teacherMapel) =>
                !empty($teacherMapel->mapel_id)
        )
        ->groupBy(
            'mapel_id'
        );

    /*
    |--------------------------------------------------------------------------
    | AMBIL DOKUMEN PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    $query = $this->protaProsemDocumentQuery();

    $ownerColumn = $this->documentOwnerColumn();

    /*
    |--------------------------------------------------------------------------
    | GURU
    |--------------------------------------------------------------------------
    |
    | Guru hanya melihat dokumen PROTA / PROSEM miliknya sendiri.
    |
    */

    if ($this->isGuru()) {

        if ($ownerColumn) {

            $query->where(
                $ownerColumn,
                Auth::id()
            );
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | NON GURU
        |--------------------------------------------------------------------------
        |
        | Wakil Kepala Sekolah / Kepala Sekolah / Admin sekolah
        | dapat melihat dokumen milik user di sekolah tersebut.
        |
        */

        $ownerIds = $this->getSchoolUserIds(
            $schoolId
        );

        if (
            $ownerColumn &&
            $ownerIds->isNotEmpty()
        ) {

            $query->whereIn(
                $ownerColumn,
                $ownerIds
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOKUMEN
    |--------------------------------------------------------------------------
    */

    $documents = $query
        ->latest('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | MAPEL YANG MEMILIKI DOKUMEN PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    $documentMapelIds = $documents
        ->map(
            fn ($document) =>
                $document->subject_id
                ??
                $document->mapel_id
                ??
                null
        )
        ->filter()
        ->map(
            fn ($id) =>
                (int) $id
        )
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | FILTER KARTU MATA PELAJARAN
    |--------------------------------------------------------------------------
    |
    | Hanya mata pelajaran yang mempunyai dokumen PROTA / PROSEM
    | yang ditampilkan pada halaman utama.
    |
    */

    $mapelGroups = $mapelGroups->filter(
        fn ($items, $mapelId) =>
            $documentMapelIds->contains(
                (int) $mapelId
            )
    );

    /*
    |--------------------------------------------------------------------------
    | HALAMAN UTAMA PROTA / PROSEM
    |--------------------------------------------------------------------------
    |
    | PENTING:
    | Jangan arahkan ke tampilan.tamp-prota-prosem.
    |
    | URL tetap:
    |
    | /registrasi-guru/prota-prosem
    |
    | Sama seperti:
    |
    | /registrasi-guru/analisis
    |
    */

    return view(
        'features.lms.school-admin.registrasi-guru.prota-prosem',
        [
            'schoolName' =>
                $schoolName,

            'schoolId' =>
                $schoolId,

            'teacherMapels' =>
                $teacherMapels,

            'mapelGroups' =>
                $mapelGroups,

            'documents' =>
                $documents,

            'academicDocuments' =>
                $documents,

            'currentRole' =>
                $this->currentRole(),

            'role' =>
                $role,

            'isGuru' =>
                $this->isGuru(),
        ]
    );
}


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM TAMPILAN
    |--------------------------------------------------------------------------
    */

public function tampilanProtaProsem(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId
) {
    $this->authorizeRole();

    $this->getSchoolProfileOrFail(
        $schoolId
    );

    /*
    |--------------------------------------------------------------------------
    | TEACHER MAPEL
    |--------------------------------------------------------------------------
    */

    $teacherMapels =
        $this->getTeacherMapel(
            $schoolId
        );

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT QUERY
    |--------------------------------------------------------------------------
    */

    $documentQuery =
        $this->protaProsemDocumentQuery();

    /*
    |--------------------------------------------------------------------------
    | ACCESS
    |--------------------------------------------------------------------------
    */

    if ($this->isGuru()) {

        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );

    } else {

        $ownerColumn =
            $this->documentOwnerColumn();

        $ownerIds =
            $this->getSchoolUserIds(
                $schoolId
            );

        if (
            $ownerColumn &&
            $ownerIds->isNotEmpty()
        ) {
            $documentQuery->whereIn(
                $ownerColumn,
                $ownerIds
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTS
    |--------------------------------------------------------------------------
    */

    $documents =
        $documentQuery
            ->with([
                'sheets.cells',
            ])
            ->latest('id')
            ->get();

    /*
     * GURU TANPA DOKUMEN:
     * Jangan biarkan Guru berhenti di halaman tampilan dengan pesan
     * "Belum ada dokumen". Arahkan selalu ke halaman input utama.
     *
     * Ini juga membuat perilaku tetap benar ketika Guru berpindah tab,
     * membuka ulang URL lama /bagan-analisis/tampilan, atau melakukan
     * refresh setelah dokumen dihapus.
     */
    if (
        $this->isGuru() &&
        $documents->isEmpty()
    ) {
        return redirect()
            ->route(
                'lms.schoolAdmin.registrasiGuru.prota-prosem',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            );
    }

    // Blade tamp-prota-prosem menggunakan $document sebagai dokumen aktif.
    // Ambil dokumen terbaru yang sudah lolos filter akses di atas.
    $document = $documents->first();

    /*
    |--------------------------------------------------------------------------
    | SHEET IDS
    |--------------------------------------------------------------------------
    |
    | academic_document_comments TIDAK memakai
    | academic_document_id.
    |
    | Komentar dihubungkan melalui:
    |
    | academic_document_sheet_id
    |
    */

    $sheetIds =
        $documents
            ->flatMap(function ($document) {

                return $document
                    ->sheets
                    ->pluck('id');
            })
            ->filter()
            ->unique()
            ->values();

    /*
    |--------------------------------------------------------------------------
    | COMMENTS
    |--------------------------------------------------------------------------
    */

    $comments =
        collect();

    if (
        $sheetIds->isNotEmpty()
    ) {

        $commentsQuery =
            AcademicDocumentComment::query()
                ->whereIn(
                    'academic_document_sheet_id',
                    $sheetIds
                )
                ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        try {

            $commentsQuery->with(
                'user'
            );

        } catch (Throwable $e) {
            // Tidak menggagalkan halaman.
        }

        $comments =
            $commentsQuery->get();
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP COMMENT
    |--------------------------------------------------------------------------
    |
    | Struktur komentar spreadsheet:
    |
    | sheet_id + coordinate
    |
    | Contoh:
    |
    | 42:B3
    | 42:C5
    |
    */

    $commentsByCell =
        $comments->groupBy(
            function ($comment) {

                return
                    $comment
                        ->academic_document_sheet_id
                    . ':'
                    .
                    $comment->coordinate;
            }
        );

    /*
    |--------------------------------------------------------------------------
    | MAPEL GROUP
    |--------------------------------------------------------------------------
    */

    $mapelGroups =
        $teacherMapels->groupBy(
            function ($teacherMapel) {

                $mapel =
                    $teacherMapel->Mapel
                    ??
                    $teacherMapel->mapel
                    ??
                    null;

                if (!$mapel) {
                    return 'Tanpa Mapel';
                }

                return
                    $mapel->nama_mapel
                    ??
                    $mapel->mata_pelajaran
                    ??
                    $mapel->nama
                    ??
                    $mapel->name
                    ??
                    'Tanpa Mapel';
            }
        );

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    |
    | FILE YANG BENAR:
    |
    | tamp-prota-prosem.blade.php
    |
    */

    return view(
        'features.lms.school-admin.registrasi-guru.tampilan.tamp-prota-prosem',
        [
            'document' =>
                $document,

            'documents' =>
                $documents,

            'academicDocuments' =>
                $documents,

            'comments' =>
                $comments,

            'commentsByCell' =>
                $commentsByCell,

            'teacherMapels' =>
                $teacherMapels,

            'mapelGroups' =>
                $mapelGroups,

            'schoolName' =>
                $schoolName,

            'schoolId' =>
                $schoolId,

            'role' =>
                $role,

            'isGuru' =>
                $this->isGuru(),

            'currentRole' =>
                $this->currentRole(),
        ]
    );
}


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM UPLOAD
    |--------------------------------------------------------------------------
    */

public function protaProsemUpload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        abort_unless(
            $this->isGuru(),
            403,
            'Hanya guru yang dapat mengupload PROTA / PROSEM.'
        );

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $teacherMapel =
            $this->getTeacherMapel(
                $schoolId
            )->first();

        abort_unless(
            $teacherMapel,
            422,
            'Guru belum memiliki mata pelajaran aktif.'
        );

        $mapelId =
            $teacherMapel->mapel_id;

        abort_unless(
            $mapelId,
            422,
            'Mata pelajaran guru belum ditemukan.'
        );

        $file =
            $request->file('file');

        $path =
            $file->store(
                'academic-documents/prota-prosem',
                'public'
            );

        DB::beginTransaction();

        try {

            $documentData = [

                'title' =>
                    $request->input(
                        'title',
                        'Program Tahunan / Program Semester'
                    ),

                'original_filename' =>
                    $file->getClientOriginalName(),

                'file_path' =>
                    $path,

                'school_partner_id' =>
                    $schoolId,

                'owner_user_id' =>
                    Auth::id(),

                'subject_id' =>
                    $mapelId,

                'document_type' =>
                    self::PROTA_PROSEM_TYPE,
            ];

            $filteredDocumentData = [];

            foreach (
                $documentData
                as $column => $value
            ) {

                if (
                    $this->documentColumnExists(
                        $column
                    )
                ) {

                    $filteredDocumentData[
                        $column
                    ] = $value;
                }
            }

            $document =
                new AcademicDocument();

            $document
                ->forceFill(
                    $filteredDocumentData
                )
                ->save();

            /*
             * Legacy fields.
             */

            $legacyData = [];

            if (
                $this->documentColumnExists(
                    'file_type'
                )
            ) {

                $legacyData['file_type'] =
                    $file->getClientMimeType();
            }

            if (
                $this->documentColumnExists(
                    'mapel_id'
                )
            ) {

                $legacyData['mapel_id'] =
                    $mapelId;
            }

            if (
                $this->documentColumnExists(
                    'uploaded_by'
                )
            ) {

                $legacyData['uploaded_by'] =
                    Auth::id();
            }

            if (
                $this->documentColumnExists(
                    'created_by'
                )
            ) {

                $legacyData['created_by'] =
                    Auth::id();
            }

            if (
                !empty($legacyData)
            ) {

                $document
                    ->forceFill(
                        $legacyData
                    )
                    ->save();
            }

            $fullPath =
                Storage::disk('public')
                    ->path($path);

            abort_unless(
                file_exists($fullPath),
                500,
                'File Excel tidak ditemukan.'
            );

            $spreadsheet =
                IOFactory::load(
                    $fullPath
                );

            $sheetIndex = 0;

            foreach (
                $spreadsheet
                    ->getWorksheetIterator()
                as $worksheet
            ) {

                $sheetIndex++;

                $sheetData = [

                    'academic_document_id' =>
                        $document->id,

                    'name' =>
                        $worksheet->getTitle(),

                    'sheet_index' =>
                        $sheetIndex,
                ];

                $filteredSheetData = [];

                foreach (
                    $sheetData
                    as $column => $value
                ) {

                    if (
                        $this->sheetColumnExists(
                            $column
                        )
                    ) {

                        $filteredSheetData[
                            $column
                        ] = $value;
                    }
                }

                $sheet =
                    new AcademicDocumentSheet();

                $sheet
                    ->forceFill(
                        $filteredSheetData
                    )
                    ->save();

                $highestRow =
                    $worksheet
                        ->getHighestRow();

                $highestColumn =
                    $worksheet
                        ->getHighestColumn();

                $highestColumnIndex =
                    Coordinate::columnIndexFromString(
                        $highestColumn
                    );

                /*
                 * Merge lookup.
                 */

                $mergedLookup = [];

                foreach (
                    $worksheet
                        ->getMergeCells()
                    as $mergeRange
                ) {

                    $parts =
                        explode(
                            ':',
                            $mergeRange
                        );

                    $startCell =
                        $parts[0] ?? null;

                    $endCell =
                        $parts[1]
                        ?? $startCell;

                    if (
                        !$startCell
                    ) {
                        continue;
                    }

                    [
                        $startColumn,
                        $startRow
                    ] =
                        Coordinate::coordinateFromString(
                            $startCell
                        );

                    [
                        $endColumn,
                        $endRow
                    ] =
                        Coordinate::coordinateFromString(
                            $endCell
                        );

                    $startColumnIndex =
                        Coordinate::columnIndexFromString(
                            $startColumn
                        );

                    $endColumnIndex =
                        Coordinate::columnIndexFromString(
                            $endColumn
                        );

                    for (
                        $r = $startRow;
                        $r <= $endRow;
                        $r++
                    ) {

                        for (
                            $c = $startColumnIndex;
                            $c <= $endColumnIndex;
                            $c++
                        ) {

                            $coordinate =
                                Coordinate::stringFromColumnIndex(
                                    $c
                                ) . $r;

                            $mergedLookup[
                                $coordinate
                            ] =
                                $mergeRange;
                        }
                    }
                }

                /*
                 * Cells.
                 */

                for (
                    $row = 1;
                    $row <= $highestRow;
                    $row++
                ) {

                    for (
                        $column = 1;
                        $column <=
                            $highestColumnIndex;
                        $column++
                    ) {

                        $cell =
                            $worksheet
                                ->getCellByColumnAndRow(
                                    $column,
                                    $row
                                );

                        $value =
                            $cell->getValue();

                        $formula = null;

                        if (
                            is_string($value) &&
                            str_starts_with(
                                $value,
                                '='
                            )
                        ) {

                            $formula =
                                $value;
                        }

                        $calculatedValue =
                            null;

                        if (
                            $formula !== null
                        ) {

                            try {

                                $calculatedValue =
                                    $cell
                                        ->getCalculatedValue();

                            } catch (
                                Throwable $e
                            ) {
                            }
                        }

                        $coordinate =
                            $cell
                                ->getCoordinate();

                        $isMerged =
                            isset(
                                $mergedLookup[
                                    $coordinate
                                ]
                            );

                        $mergeRange =
                            $mergedLookup[
                                $coordinate
                            ]
                            ?? null;

                        $dataType = null;

                        try {

                            $dataType =
                                $cell
                                    ->getDataType();

                        } catch (
                            Throwable $e
                        ) {
                        }

                        $style =
                            $this->extractCellStyle(
                                $cell
                            );

                        $hasStyle = false;

                        try {

                            $hasStyle =
                                $cell
                                    ->getStyle()
                                    ->getFill()
                                    ->getFillType()
                                    !== Fill::FILL_NONE;

                        } catch (
                            Throwable $e
                        ) {
                        }

                        if (
                            $value === null &&
                            !$hasStyle &&
                            !$isMerged
                        ) {
                            continue;
                        }

                        $cellData = [

                            'academic_document_sheet_id' =>
                                $sheet->id,

                            'coordinate' =>
                                $coordinate,

                            'row_number' =>
                                $row,

                            'column_number' =>
                                $column,

                            'column_index' =>
                                $column,

                            'value' =>
                                $formula !== null
                                    ? null
                                    : $value,

                            'formula' =>
                                $formula,

                            'calculated_value' =>
                                $calculatedValue,

                            'data_type' =>
                                $dataType,

                            'style' =>
                                !empty($style)
                                    ? json_encode(
                                        $style,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    )
                                    : null,

                            'is_merged' =>
                                $isMerged,

                            'merge_range' =>
                                $mergeRange,
                        ];

                        foreach (
                            [
                                'column_index',
                                'formula',
                                'calculated_value',
                                'data_type',
                                'style',
                                'is_merged',
                                'merge_range',
                            ]
                            as $optionalColumn
                        ) {

                            if (
                                !$this->cellColumnExists(
                                    $optionalColumn
                                )
                            ) {

                                unset(
                                    $cellData[
                                        $optionalColumn
                                    ]
                                );
                            }
                        }

                        $academicCell =
                            new AcademicDocumentCell();

                        $academicCell
                            ->forceFill(
                                $cellData
                            )
                            ->save();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route(
                    'lms.schoolAdmin.registrasiGuru.baganAnalisis.tampilan',
                    [
                        'role' =>
                            $role,

                        'schoolName' =>
                            $schoolName,

                        'schoolId' =>
                            $schoolId,

                        'mapel_id' =>
                            $mapelId,
                    ]
                )
                ->with(
                    'success',
                    'PROTA / PROSEM berhasil diupload.'
                );

        } catch (Throwable $e) {

            DB::rollBack();

            if (
                isset($path) &&
                $path &&
                Storage::disk('public')
                    ->exists($path)
            ) {

                Storage::disk('public')
                    ->delete($path);
            }

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM DOWNLOAD
    |--------------------------------------------------------------------------
    */

public function protaProsemDownload(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $academicDocument =
            $this->getDocumentForSchoolOrFail(
                $schoolId,
                $document,
                self::PROTA_PROSEM_TYPE,
                $this->isGuru()
            );

        abort_unless(
            $academicDocument->file_path,
            404,
            'File dokumen tidak ditemukan.'
        );

        $filePath =
            Storage::disk('public')
                ->path(
                    $academicDocument->file_path
                );

        abort_unless(
            file_exists($filePath),
            404,
            'File dokumen tidak ditemukan di storage.'
        );

        return response()->download(
            $filePath,
            $academicDocument->original_filename
                ?: 'prota-prosem.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM DELETE
    |--------------------------------------------------------------------------
    */

public function protaProsemDestroy(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId,
    int $document
) {
    $this->authorizeRole();

    $this->getSchoolProfileOrFail(
        $schoolId
    );

    abort_unless(
        $this->isGuru(),
        403,
        'Hanya guru yang dapat menghapus PROTA / PROSEM.'
    );

    /*
    |--------------------------------------------------------------------------
    | PASTIKAN DOCUMENT YANG DIMINTA MEMANG MILIK GURU
    |--------------------------------------------------------------------------
    */

    $ownerColumn = $this->documentOwnerColumn();

    abort_unless(
        $ownerColumn,
        500,
        'Kolom pemilik dokumen belum tersedia pada tabel academic_documents.'
    );

    /*
    |--------------------------------------------------------------------------
    | CARI DOKUMEN YANG DIKLIK
    |--------------------------------------------------------------------------
    */

    $selectedDocument = $this->protaProsemDocumentQuery()
        ->where(
            'id',
            $document
        )
        ->where(
            $ownerColumn,
            Auth::id()
        )
        ->firstOrFail();

    /*
    |--------------------------------------------------------------------------
    | AMBIL SEMUA DOKUMEN PROTA / PROSEM MILIK GURU
    |--------------------------------------------------------------------------
    |
    | PROTA / PROSEM pada halaman ini menggunakan satu dokumen aktif.
    | Jadi setelah Guru memilih Hapus, seluruh dokumen PROTA / PROSEM
    | milik Guru dibersihkan agar halaman benar-benar kembali ke mode Upload.
    |
    */

    $documentsToDelete = $this->protaProsemDocumentQuery()
        ->where(
            $ownerColumn,
            Auth::id()
        )
        ->get();

    DB::beginTransaction();

    try {

        foreach ($documentsToDelete as $academicDocument) {

            /*
            |--------------------------------------------------------------------------
            | SHEET IDS
            |--------------------------------------------------------------------------
            */

            $sheetIds = AcademicDocumentSheet::query()
                ->where(
                    'academic_document_id',
                    $academicDocument->id
                )
                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | DELETE COMMENTS
            |--------------------------------------------------------------------------
            */

            if ($sheetIds->isNotEmpty()) {

                if (
                    $this->commentColumnExists(
                        'academic_document_sheet_id'
                    )
                ) {

                    AcademicDocumentComment::query()
                        ->whereIn(
                            'academic_document_sheet_id',
                            $sheetIds
                        )
                        ->delete();
                }

                /*
                |--------------------------------------------------------------------------
                | DELETE CELLS
                |--------------------------------------------------------------------------
                */

                AcademicDocumentCell::query()
                    ->whereIn(
                        'academic_document_sheet_id',
                        $sheetIds
                    )
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE SHEETS
            |--------------------------------------------------------------------------
            */

            AcademicDocumentSheet::query()
                ->where(
                    'academic_document_id',
                    $academicDocument->id
                )
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE EXCEL
            |--------------------------------------------------------------------------
            */

            if (
                !empty($academicDocument->file_path) &&
                Storage::disk('public')->exists(
                    $academicDocument->file_path
                )
            ) {

                Storage::disk('public')->delete(
                    $academicDocument->file_path
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE DOCUMENT
            |--------------------------------------------------------------------------
            */

            $academicDocument->delete();
        }

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | KEMBALI KE HALAMAN PROTA / PROSEM
        |--------------------------------------------------------------------------
        |
        | Karena semua dokumen Guru sudah dibersihkan,
        | $document pada halaman berikutnya akan menjadi null.
        |
        */

        return redirect()
            ->route(
                'lms.schoolAdmin.registrasiGuru.prota-prosem',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            )
            ->with(
                'success',
                'PROTA / PROSEM berhasil dihapus. Silakan upload dokumen baru.'
            );

    } catch (Throwable $e) {

        DB::rollBack();

        throw $e;
    }
}

    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM SHOW
    |--------------------------------------------------------------------------
    */

public function showProtaProsem(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $academicDocument =
            $this->getDocumentForSchoolOrFail(
                $schoolId,
                $document,
                self::PROTA_PROSEM_TYPE,
                $this->isGuru()
            );

        $academicDocument->load([
            'sheets' => function ($query) {

                $query->orderBy(
                    'sheet_index'
                );
            },

            'sheets.cells',
        ]);

        $sheetIds =
            $academicDocument
                ->sheets
                ->pluck('id');

        $comments =
            collect();

        if (
            $sheetIds->isNotEmpty() &&
            $this->commentColumnExists(
                'academic_document_sheet_id'
            )
        ) {

            $comments =
                AcademicDocumentComment::query()
                    ->with('user')
                    ->whereIn(
                        'academic_document_sheet_id',
                        $sheetIds
                    )
                    ->latest('id')
                    ->get();
        }

        return view(
            'features.lms.school-admin.registrasi-guru.tampilan.tamp-prota-prosem',
            [
                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'document' =>
                    $academicDocument,

                'academicDocuments' =>
                    collect([
                        $academicDocument,
                    ]),

                'documents' =>
                    collect([
                        $academicDocument,
                    ]),

                'comments' =>
                    $comments,

                'mapel' =>
                    null,

                'mapelName' =>
                    'Mata Pelajaran',

                'currentRole' =>
                    $this->currentRole(),

                'isGuru' =>
                    $this->isGuru(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM COMMENTS ALIAS
    |--------------------------------------------------------------------------
    */

public function protaProsemComments(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ): JsonResponse {
        return $this->comments(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $cell
        );
    }


    public function storeProtaProsemComment(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId,
    int $cell
) {
    $this->authorizeRole();

    $this->getSchoolProfileOrFail(
        $schoolId
    );

    $request->validate([
        'academic_document_cell_id' => [
            'required',
            'integer',
            'exists:academic_document_cells,id',
        ],

        'comment_text' => [
            'required',
            'string',
            'max:5000',
        ],

        'selected_text' => [
            'nullable',
            'string',
            'max:10000',
        ],

        'anchor_key' => [
            'nullable',
            'string',
            'max:255',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | CELL ID HARUS SAMA DENGAN ROUTE
    |--------------------------------------------------------------------------
    */

    abort_unless(
        (int) $request->academic_document_cell_id
        ===
        (int) $cell,

        422,

        'Cell komentar tidak sesuai.'
    );

    /*
    |--------------------------------------------------------------------------
    | CELL -> SHEET
    |--------------------------------------------------------------------------
    */

    $academicCell =
        AcademicDocumentCell::query()
            ->with('sheet')
            ->findOrFail($cell);

    $sheet =
        $academicCell->sheet;

    abort_unless(
        $sheet,
        404,
        'Sheet dokumen tidak ditemukan.'
    );

    /*
    |--------------------------------------------------------------------------
    | COORDINATE WAJIB
    |--------------------------------------------------------------------------
    */

    abort_unless(
        filled($academicCell->coordinate),
        422,
        'Coordinate cell tidak ditemukan.'
    );

    /*
    |--------------------------------------------------------------------------
    | SHEET -> DOCUMENT
    |--------------------------------------------------------------------------
    */

    $documentQuery =
        $this->protaProsemDocumentQuery();

    $documentQuery->where(
        'id',
        $sheet->academic_document_id
    );

    /*
    |--------------------------------------------------------------------------
    | OWNER ACCESS
    |--------------------------------------------------------------------------
    */

    if (
        $this->isGuru()
    ) {

        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );

    } else {

        $ownerColumn =
            $this->documentOwnerColumn();

        $ownerIds =
            $this->getSchoolUserIds(
                $schoolId
            );

        if (
            $ownerColumn &&
            $ownerIds->isNotEmpty()
        ) {

            $documentQuery->whereIn(
                $ownerColumn,
                $ownerIds
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT
    |--------------------------------------------------------------------------
    */

    $document =
        $documentQuery->first();

    abort_unless(
        $document,
        404,
        'Dokumen PROTA/PROSEM tidak ditemukan.'
    );

    /*
    |--------------------------------------------------------------------------
    | PASTIKAN SHEET MILIK DOCUMENT
    |--------------------------------------------------------------------------
    */

    abort_unless(
        (int) $sheet->academic_document_id
        ===
        (int) $document->id,

        404,

        'Sheet tidak termasuk dokumen ini.'
    );

    /*
    |--------------------------------------------------------------------------
    | COMMENT DATA
    |--------------------------------------------------------------------------
    */

    $commentText =
        $request->input(
            'comment_text'
        );

    $commentData = [

        'academic_document_id' =>
            $document->id,

        'academic_document_sheet_id' =>
            $sheet->id,

        'academic_document_cell_id' =>
            $academicCell->id,

        'coordinate' =>
            $academicCell->coordinate,

        'user_id' =>
            Auth::id(),

        /*
         * DATABASE MEMAKAI "comment"
         */
        'comment' =>
            $commentText,

        /*
         * Tetap kompatibel jika kolom ini ada.
         */
        'comment_text' =>
            $commentText,

        'selected_text' =>
            $request->input(
                'selected_text'
            ),

        'anchor_key' =>
            $request->input(
                'anchor_key'
            ),

        'is_resolved' =>
            false,
    ];

    /*
    |--------------------------------------------------------------------------
    | FILTER SESUAI KOLOM DATABASE
    |--------------------------------------------------------------------------
    */

    $filteredData = [];

    foreach (
        $commentData as $column => $value
    ) {

        if (
            $this->commentColumnExists(
                $column
            )
        ) {

            $filteredData[$column] =
                $value;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE REQUIRED COLUMNS
    |--------------------------------------------------------------------------
    */

    $filteredData[
        'academic_document_sheet_id'
    ] =
        $sheet->id;

    $filteredData[
        'coordinate'
    ] =
        $academicCell->coordinate;

    $filteredData[
        'comment'
    ] =
        $commentText;

    /*
    |--------------------------------------------------------------------------
    | SIMPAN
    |--------------------------------------------------------------------------
    */

    $comment =
        new AcademicDocumentComment();

    $comment->forceFill(
        $filteredData
    );

    $comment->save();

    /*
    |--------------------------------------------------------------------------
    | LOAD USER
    |--------------------------------------------------------------------------
    */

    try {

        $comment->load(
            'user'
        );

    } catch (Throwable $e) {
        // Jangan gagalkan penyimpanan.
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' =>
            true,

        'message' =>
            'Komentar PROTA/PROSEM berhasil ditambahkan.',

        'comment' => [

            'id' =>
                $comment->id,

            'academic_document_id' =>
                $comment->academic_document_id,

            'academic_document_sheet_id' =>
                $comment->academic_document_sheet_id,

            'academic_document_cell_id' =>
                $comment->academic_document_cell_id,

            'coordinate' =>
                $comment->coordinate,

            'user_id' =>
                $comment->user_id,

            'user_name' =>
                $comment->user?->name
                ??
                $comment->user?->nama
                ??
                'Pengguna',

            'comment_text' =>
                $comment->comment_text
                ??
                $comment->comment
                ??
                '',

            'comment' =>
                $comment->comment
                ??
                $comment->comment_text
                ??
                '',

            'selected_text' =>
                $comment->selected_text,

            'anchor_key' =>
                $comment->anchor_key,

            'is_resolved' =>
                (bool) (
                    $comment->is_resolved
                    ??
                    false
                ),

            'created_at' =>
                optional(
                    $comment->created_at
                )->format(
                    'd M Y H:i'
                ),
        ],
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM UPDATE CELL
    |--------------------------------------------------------------------------
    */

    public function updateCell(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        abort_unless(
            $this->isGuru(),
            403,
            'Hanya guru yang dapat mengubah cell.'
        );

        $academicCell =
            AcademicDocumentCell::query()
                ->with('sheet')
                ->findOrFail($cell);

        $sheet =
            $academicCell->sheet;

        abort_unless(
            $sheet,
            404,
            'Sheet dokumen tidak ditemukan.'
        );

        $documentQuery =
            $this->protaProsemDocumentQuery();

        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );

        $document =
            $documentQuery
                ->where(
                    'id',
                    $sheet->academic_document_id
                )
                ->first();

        abort_unless(
            $document,
            404,
            'Dokumen PROTA/PROSEM tidak ditemukan.'
        );

        $request->validate([
            'value' => [
                'nullable',
            ],
        ]);

        $value =
            $request->input(
                'value'
            );

        $data = [
            'value' =>
                $value,
        ];

        if (
            $this->cellColumnExists(
                'calculated_value'
            )
        ) {

            $data['calculated_value'] =
                $value;
        }

        $academicCell
            ->forceFill(
                $data
            )
            ->save();

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Cell PROTA/PROSEM berhasil diperbarui.',

            'cell' =>
                $academicCell->fresh(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA / PROSEM COMMENT LIST
    |--------------------------------------------------------------------------
    */

    public function comments(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $cell
    ): JsonResponse {
        $this->authorizeRole();

        $academicCell =
            AcademicDocumentCell::query()
                ->with('sheet')
                ->findOrFail($cell);

        $sheet =
            $academicCell->sheet;

        abort_unless(
            $sheet,
            404,
            'Sheet tidak ditemukan.'
        );

        $documentQuery =
            $this->protaProsemDocumentQuery()
                ->where(
                    'id',
                    $sheet->academic_document_id
                );

        if (
            $this->isGuru()
        ) {

            $this->applyDocumentOwner(
                $documentQuery,
                (int) Auth::id()
            );
        }

        $document =
            $documentQuery->firstOrFail();

        $comments =
            AcademicDocumentComment::query()
                ->with('user')
                ->where(
                    'academic_document_cell_id',
                    $academicCell->id
                )
                ->latest('id')
                ->get()
                ->map(
                    function ($comment) {

                        return [
                            'id' =>
                                $comment->id,

                            'user_id' =>
                                $comment->user_id,

                            'user_name' =>
                                $comment->user?->name
                                ??
                                $comment->user?->nama
                                ??
                                'Pengguna',

                            'comment_text' =>
                                $comment->comment_text
                                ??
                                $comment->comment
                                ??
                                '',

                            'selected_text' =>
                                $comment->selected_text,

                            'anchor_key' =>
                                $comment->anchor_key,

                            'is_resolved' =>
                                (bool) (
                                    $comment->is_resolved
                                    ??
                                    false
                                ),

                            'created_at' =>
                                optional(
                                    $comment->created_at
                                )->format(
                                    'd M Y H:i'
                                ),
                        ];
                    }
                )
                ->values();

        return response()->json([
            'success' =>
                true,

            'document_id' =>
                $document->id,

            'cell_id' =>
                $academicCell->id,

            'comments' =>
                $comments,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BAGAN ANALISIS COMMENT LIST
    |--------------------------------------------------------------------------
    */

    public function tampilanBaganAnalisis(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId
) {
    $this->authorizeRole();

    $this->getSchoolProfileOrFail(
        $schoolId
    );

    /*
    |--------------------------------------------------------------------------
    | TEACHER MAPEL
    |--------------------------------------------------------------------------
    */

    $teacherMapels =
        $this->getTeacherMapel(
            $schoolId
        );

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT QUERY
    |--------------------------------------------------------------------------
    */

    $documentQuery =
        $this->baganAnalisisDocumentQuery();

    /*
    |--------------------------------------------------------------------------
    | ACCESS
    |--------------------------------------------------------------------------
    */

    if ($this->isGuru()) {

        $this->applyDocumentOwner(
            $documentQuery,
            (int) Auth::id()
        );

    } else {

        $ownerColumn =
            $this->documentOwnerColumn();

        $ownerIds =
            $this->getSchoolUserIds(
                $schoolId
            );

        if (
            $ownerColumn &&
            $ownerIds->isNotEmpty()
        ) {
            $documentQuery->whereIn(
                $ownerColumn,
                $ownerIds
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTS
    |--------------------------------------------------------------------------
    */

    $documents =
        $documentQuery
            ->with([
                'sheets.cells',
            ])
            ->latest('id')
            ->get();

    /*
     * GURU TANPA DOKUMEN:
     * Jangan biarkan Guru berhenti di halaman tampilan dengan pesan
     * "Belum ada dokumen". Arahkan selalu ke halaman input utama.
     *
     * Ini juga membuat perilaku tetap benar ketika Guru berpindah tab,
     * membuka ulang URL lama /bagan-analisis/tampilan, atau melakukan
     * refresh setelah dokumen dihapus.
     */
    if (
        $this->isGuru() &&
        $documents->isEmpty()
    ) {
        return redirect()
            ->route(
                'lms.schoolAdmin.registrasiGuru.baganAnalisis',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            );
    }

    // Blade tamp-analisis menggunakan $document sebagai dokumen aktif.
    // Ambil dokumen terbaru yang sudah lolos filter akses di atas.
    $document = $documents->first();

    /*
    |--------------------------------------------------------------------------
    | SHEET IDS
    |--------------------------------------------------------------------------
    |
    | academic_document_comments TIDAK memakai
    | academic_document_id.
    |
    | Komentar dihubungkan melalui:
    |
    | academic_document_sheet_id
    |
    */

    $sheetIds =
        $documents
            ->flatMap(function ($document) {

                return $document
                    ->sheets
                    ->pluck('id');
            })
            ->filter()
            ->unique()
            ->values();

    /*
    |--------------------------------------------------------------------------
    | COMMENTS
    |--------------------------------------------------------------------------
    */

    $comments =
        collect();

    if (
        $sheetIds->isNotEmpty()
    ) {

        $commentsQuery =
            AcademicDocumentComment::query()
                ->whereIn(
                    'academic_document_sheet_id',
                    $sheetIds
                )
                ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        try {

            $commentsQuery->with(
                'user'
            );

        } catch (Throwable $e) {
            // Tidak menggagalkan halaman.
        }

        $comments =
            $commentsQuery->get();
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP COMMENT
    |--------------------------------------------------------------------------
    |
    | Struktur komentar spreadsheet:
    |
    | sheet_id + coordinate
    |
    | Contoh:
    |
    | 42:B3
    | 42:C5
    |
    */

    $commentsByCell =
        $comments->groupBy(
            function ($comment) {

                return
                    $comment
                        ->academic_document_sheet_id
                    . ':'
                    .
                    $comment->coordinate;
            }
        );

    /*
    |--------------------------------------------------------------------------
    | MAPEL GROUP
    |--------------------------------------------------------------------------
    */

    $mapelGroups =
        $teacherMapels->groupBy(
            function ($teacherMapel) {

                $mapel =
                    $teacherMapel->Mapel
                    ??
                    $teacherMapel->mapel
                    ??
                    null;

                if (!$mapel) {
                    return 'Tanpa Mapel';
                }

                return
                    $mapel->nama_mapel
                    ??
                    $mapel->mata_pelajaran
                    ??
                    $mapel->nama
                    ??
                    $mapel->name
                    ??
                    'Tanpa Mapel';
            }
        );

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    |
    | FILE YANG BENAR:
    |
    | tamp-analisis.blade.php
    |
    */

    return view(
        'features.lms.school-admin.registrasi-guru.tampilan.tamp-analisis',
        [
            'document' =>
                $document,

            'documents' =>
                $documents,

            'academicDocuments' =>
                $documents,

            'comments' =>
                $comments,

            'commentsByCell' =>
                $commentsByCell,

            'teacherMapels' =>
                $teacherMapels,

            'mapelGroups' =>
                $mapelGroups,

            'schoolName' =>
                $schoolName,

            'schoolId' =>
                $schoolId,

            'role' =>
                $role,

            'isGuru' =>
                $this->isGuru(),

            'currentRole' =>
                $this->currentRole(),
        ]
    );
}

    /*
    |--------------------------------------------------------------------------
    | ANALISIS COMMENT LIST
    |--------------------------------------------------------------------------
    */

    public function analisisComments(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId,
    int $cell
): JsonResponse {
    $this->authorizeRole($role);

    $academicCell = AcademicDocumentCell::query()
        ->with([
            'sheet.document',
        ])
        ->findOrFail($cell);

    $sheet = $academicCell->sheet;

    abort_unless(
        $sheet !== null,
        404,
        'Sheet dokumen tidak ditemukan.'
    );

    $document = $sheet->document;

    abort_unless(
        $document !== null,
        404,
        'Dokumen tidak ditemukan.'
    );

    /*
     * Pastikan dokumen memang berada pada sekolah
     * yang sedang dibuka.
     */
    $this->getDocumentForSchoolOrFail(
        $document->id,
        $schoolId,
        self::BAGAN_ANALISIS_TYPE
    );

    /*
     * Schema komentar project kamu menggunakan:
     *
     * academic_document_sheet_id
     * coordinate
     * user_id
     * comment
     *
     * BUKAN:
     * academic_document_id
     * academic_document_cell_id
     * comment_text
     */
    $comments = AcademicDocumentComment::query()
        ->where(
            'academic_document_sheet_id',
            $sheet->id
        )
        ->where(
            'coordinate',
            $academicCell->coordinate
        )
        ->with('user')
        ->latest('id')
        ->get();

    return response()->json([
        'success' => true,

        'cell_id' =>
            $academicCell->id,

        'sheet_id' =>
            $sheet->id,

        'coordinate' =>
            $academicCell->coordinate,

        'comments' =>
            $comments->map(function ($comment) {

                return [
                    'id' =>
                        $comment->id,

                    'academic_document_sheet_id' =>
                        $comment->academic_document_sheet_id,

                    'coordinate' =>
                        $comment->coordinate,

                    /*
                     * Alias untuk JavaScript/Blade lama.
                     */
                    'comment_text' =>
                        $comment->comment ?? '',

                    /*
                     * Kolom database sebenarnya.
                     */
                    'comment' =>
                        $comment->comment ?? '',

                    'user_id' =>
                        $comment->user_id,

                    'user_name' =>
                        $comment->user?->name
                        ?? $comment->user?->nama
                        ?? $comment->user?->username
                        ?? 'Pengguna',

                    'is_resolved' =>
                        (bool) (
                            $comment->is_resolved
                            ?? false
                        ),

                    'is_revision' =>
                        (bool) (
                            $comment->is_revision
                            ?? false
                        ),

                    'created_at' =>
                        optional(
                            $comment->created_at
                        )->toISOString(),

                    'updated_at' =>
                        optional(
                            $comment->updated_at
                        )->toISOString(),
                ];
            })->values(),
        'count' =>
            $comments->count(),
    ]);
}


    /*
    |--------------------------------------------------------------------------
    | RESOLVE PROTA REVISION
    |--------------------------------------------------------------------------
    */

    public function resolveRevision(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $comment
    ) {
        $this->authorizeRole();
        $this->getSchoolProfileOrFail($schoolId);

        $commentModel =
            AcademicDocumentComment::query()
                ->findOrFail($comment);

        $sheet =
            AcademicDocumentSheet::query()
                ->findOrFail($commentModel->academic_document_sheet_id);

        $documentQuery =
            $this->protaProsemDocumentQuery()
                ->where('id', $sheet->academic_document_id);

        if ($this->isGuru()) {
            $this->applyDocumentOwner(
                $documentQuery,
                (int) Auth::id()
            );
        } else {
            $ownerColumn = $this->documentOwnerColumn();
            $ownerIds = $this->getSchoolUserIds($schoolId);

            if ($ownerColumn && $ownerIds->isNotEmpty()) {
                $documentQuery->whereIn($ownerColumn, $ownerIds);
            }
        }

        $document = $documentQuery->firstOrFail();

        abort_unless(
            (int) $sheet->academic_document_id === (int) $document->id,
            404,
            'Komentar bukan milik dokumen PROTA/PROSEM ini.'
        );

        if ($this->commentColumnExists('is_resolved')) {
            $commentModel->forceFill([
                'is_resolved' => true,
            ]);
        }

        if ($this->commentColumnExists('resolved_by')) {
            $commentModel->forceFill([
                'resolved_by' => Auth::id(),
            ]);
        }

        if ($this->commentColumnExists('resolved_at')) {
            $commentModel->forceFill([
                'resolved_at' => now(),
            ]);
        }

        $commentModel->save();

        if ($this->cellColumnExists('has_revision')) {
            $coordinate = strtoupper(
                trim((string) $commentModel->coordinate)
            );

            $hasOpenRevision =
                AcademicDocumentComment::query()
                    ->where(
                        'academic_document_sheet_id',
                        $sheet->id
                    )
                    ->whereRaw(
                        'UPPER(TRIM(coordinate)) = ?',
                        [$coordinate]
                    )
                    ->where('is_revision', true)
                    ->where(function ($query) {
                        $query
                            ->whereNull('is_resolved')
                            ->orWhere('is_resolved', false);
                    })
                    ->exists();

            $cellModel =
                AcademicDocumentCell::query()
                    ->where(
                        'academic_document_sheet_id',
                        $sheet->id
                    )
                    ->whereRaw(
                        'UPPER(TRIM(coordinate)) = ?',
                        [$coordinate]
                    )
                    ->first();

            if ($cellModel) {
                $cellModel->forceFill([
                    'has_revision' => $hasOpenRevision,
                ])->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Revisi PROTA / PROSEM berhasil diselesaikan.',
            'comment_id' => $commentModel->id,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    public function resolveBaganAnalisisRevision(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $comment
    ) {
        $this->authorizeRole();
        $this->getSchoolProfileOrFail($schoolId);
    
        $commentModel = AcademicDocumentComment::query()->findOrFail($comment);
        $sheet = AcademicDocumentSheet::query()->findOrFail($commentModel->academic_document_sheet_id);
    
        $documentQuery = $this->baganAnalisisDocumentQuery()->where('id', $sheet->academic_document_id);
    
        if ($this->isGuru()) {
            $this->applyDocumentOwner($documentQuery, (int) Auth::id());
        } else {
            $ownerColumn = $this->documentOwnerColumn();
            $ownerIds = $this->getSchoolUserIds($schoolId);
            if ($ownerColumn && $ownerIds->isNotEmpty()) {
                $documentQuery->whereIn($ownerColumn, $ownerIds);
            }
        }
    
        $document = $documentQuery->firstOrFail();
    
        abort_unless(
            (int) $sheet->academic_document_id === (int) $document->id,
            404,
            'Komentar bukan milik dokumen Bagan Analisis ini.'
        );
    
        if ($this->commentColumnExists('is_resolved')) {
            $commentModel->forceFill(['is_resolved' => true]);
        }
        if ($this->commentColumnExists('resolved_by')) {
            $commentModel->forceFill(['resolved_by' => Auth::id()]);
        }
        if ($this->commentColumnExists('resolved_at')) {
            $commentModel->forceFill(['resolved_at' => now()]);
        }
    
        $commentModel->save();
    
        if ($this->cellColumnExists('has_revision')) {
            $coordinate = strtoupper(trim((string) $commentModel->coordinate));
    
            $hasOpenRevision = AcademicDocumentComment::query()
                ->where('academic_document_sheet_id', $sheet->id)
                ->whereRaw('UPPER(TRIM(coordinate)) = ?', [$coordinate])
                ->where('is_revision', true)
                ->where(function ($query) {
                    $query->whereNull('is_resolved')->orWhere('is_resolved', false);
                })
                ->exists();
    
            $cellModel = AcademicDocumentCell::query()
                ->where('academic_document_sheet_id', $sheet->id)
                ->whereRaw('UPPER(TRIM(coordinate)) = ?', [$coordinate])
                ->first();
    
            if ($cellModel) {
                $cellModel->forceFill(['has_revision' => $hasOpenRevision])->save();
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Revisi Bagan Analisis berhasil diselesaikan.',
            'comment_id' => $commentModel->id,
        ]);
    }
    
        public function resolveAnalisisRevision(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $comment
    ) {
        return $this->resolveBaganAnalisisRevision(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $comment
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW ANALISIS
    |--------------------------------------------------------------------------
    */

    public function showAnalisis(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $document
    ) {
        $this->authorizeRole();

        $this->getSchoolProfileOrFail(
            $schoolId
        );

        $academicDocument =
            $this->getDocumentForSchoolOrFail(
                $schoolId,
                $document,
                self::BAGAN_ANALISIS_TYPE,
                $this->isGuru()
            );

        $academicDocument->load([
            'sheets' => function ($query) {

                $query->orderBy(
                    'sheet_index'
                );
            },

            'sheets.cells',
        ]);

        $sheetIds =
            $academicDocument
                ->sheets
                ->pluck('id');

        $comments =
            collect();

        if (
            $sheetIds->isNotEmpty() &&
            $this->commentColumnExists(
                'academic_document_sheet_id'
            )
        ) {

            $comments =
                AcademicDocumentComment::query()
                    ->with('user')
                    ->whereIn(
                        'academic_document_sheet_id',
                        $sheetIds
                    )
                    ->latest('id')
                    ->get();
        }

        return view(
            'features.lms.school-admin.registrasi-guru.tampilan.tamp-analisis',
            [
                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'document' =>
                    $academicDocument,

                'academicDocuments' =>
                    collect([
                        $academicDocument,
                    ]),

                'documents' =>
                    collect([
                        $academicDocument,
                    ]),

                'comments' =>
                    $comments,

                'mapel' =>
                    null,

                'mapelName' =>
                    'Mata Pelajaran',

                'currentRole' =>
                    $this->currentRole(),

                'isGuru' =>
                    $this->isGuru(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROTA/PROSEM DOCUMENT ROUTE HELPERS
    |--------------------------------------------------------------------------
    |
    | Method-method upload/download/delete/show yang sudah ada di controller
    | kamu tetap dapat memakai query/helper di bawah.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC DRIVE - SAVE / BROWSE
    |--------------------------------------------------------------------------
    */

    public function saveArchive(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        string $type,
        int $document
    ): JsonResponse {
        $this->authorizeRole();
        $this->getSchoolProfileOrFail($schoolId);

        abort_unless($this->isGuru(), 403, 'Hanya guru yang dapat menyimpan dokumen ke Drive.');

        $documentType = match (strtolower($type)) {
            'analisis' => self::BAGAN_ANALISIS_TYPE,
            'prota', 'prota-prosem' => self::PROTA_PROSEM_TYPE,
            default => abort(404, 'Jenis dokumen tidak ditemukan.'),
        };

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publish' => ['nullable', 'boolean'],
        ]);

        $academicDocument = $this->getDocumentForSchoolOrFail(
            $schoolId,
            $document,
            $documentType,
            true
        );

        $updates = ['title' => trim($request->string('title')->toString())];

        if ($this->documentColumnExists('status')) {
            $updates['status'] = 'saved';
        }
        if ($this->documentColumnExists('saved_at')) {
            $updates['saved_at'] = now();
        }

        $academicDocument->forceFill($updates)->save();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil disimpan ke Academic Drive.',
            'document_id' => $academicDocument->id,
            'status' => $this->documentColumnExists('status') ? $academicDocument->status : 'saved',
        ]);
    }

    public function browseAcademicDocuments(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        string $type
    ): JsonResponse {
        $this->authorizeRole();
        $this->getSchoolProfileOrFail($schoolId);

        $documentType = match (strtolower($type)) {
            'analisis' => self::BAGAN_ANALISIS_TYPE,
            'prota', 'prota-prosem' => self::PROTA_PROSEM_TYPE,
            default => abort(404, 'Jenis dokumen tidak ditemukan.'),
        };

        $query = AcademicDocument::query();

        if ($this->documentColumnExists('school_partner_id')) {
            $query->where('school_partner_id', $schoolId);
        }
        if ($this->documentColumnExists('document_type')) {
            $query->where('document_type', $documentType);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('original_filename', 'like', '%' . $search . '%');
            });
        }

        $documents = $query->latest('id')->limit(50)->get();

        $ownerColumn = $this->documentOwnerColumn();
        $ownerIds = $ownerColumn
            ? $documents->pluck($ownerColumn)->filter()->unique()->map(fn ($id) => (int) $id)->values()
            : collect();

        $owners = collect();
        if ($ownerIds->isNotEmpty() && Schema::hasTable('user_accounts')) {
            $owners = DB::table('user_accounts')->whereIn('id', $ownerIds->all())->get()->keyBy('id');
        }

        $base = url('/lms/' . rawurlencode($role) . '/' . rawurlencode($schoolName) . '/' . rawurlencode((string) $schoolId) . '/registrasi-guru/academic-document/' . $type);

        return response()->json([
            'success' => true,
            'documents' => $documents->map(function ($doc) use ($ownerColumn, $owners, $base) {
                $ownerId = $ownerColumn ? (int) ($doc->{$ownerColumn} ?? 0) : 0;
                $owner = $owners->get($ownerId);
                $ownerName = $owner?->name ?? $owner?->nama ?? $owner?->nama_lengkap ?? $owner?->username ?? $owner?->email ?? 'Pengguna';
                $isSaved = $this->documentColumnExists('status')
                    ? strtolower((string) ($doc->status ?? '')) === 'saved'
                    : (bool) ($doc->saved_at ?? false);

                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'original_filename' => $doc->original_filename,
                    'owner_name' => $ownerName,
                    'owner_user_id' => $ownerId,
                    'is_saved' => $isSaved,
                    'updated_at_label' => optional($doc->updated_at)->format('d/m/Y H:i'),
                    'view_url' => $base . '/' . $doc->id . '/view',
                ];
            })->values(),
        ]);
    }

    public function viewSharedAcademicDocument(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        string $type,
        int $document
    ) {
        $this->authorizeRole();
        $this->getSchoolProfileOrFail($schoolId);

        $documentType = match (strtolower($type)) {
            'analisis' => self::BAGAN_ANALISIS_TYPE,
            'prota', 'prota-prosem' => self::PROTA_PROSEM_TYPE,
            default => abort(404, 'Jenis dokumen tidak ditemukan.'),
        };

        $academicDocument = $this->getDocumentForSchoolOrFail(
            $schoolId, $document, $documentType, false
        );

        $academicDocument->load(['sheets' => fn ($q) => $q->orderBy('sheet_index'), 'sheets.cells']);

        $sheetIds = $academicDocument->sheets->pluck('id');
        $comments = collect();
        if ($sheetIds->isNotEmpty() && $this->commentColumnExists('academic_document_sheet_id')) {
            $comments = AcademicDocumentComment::query()->with('user')->whereIn('academic_document_sheet_id', $sheetIds)->latest('id')->get();
        }

        return view(
            'features.lms.school-admin.registrasi-guru.tampilan.' . ($documentType === self::BAGAN_ANALISIS_TYPE ? 'tamp-analisis' : 'tamp-prota-prosem'),
            [
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'document' => $academicDocument,
                'academicDocuments' => collect([$academicDocument]),
                'documents' => collect([$academicDocument]),
                'comments' => $comments,
                'mapel' => null,
                'mapelName' => 'Mata Pelajaran',
                'currentRole' => $this->currentRole(),
                'isGuru' => false,
                'role' => $role,
            ]
        );
    }


    protected function getProtaProsemDocumentOrFail(
        int $schoolId,
        int $documentId,
        bool $ownerOnly = false
    ) {
        $query =
            $this->protaProsemDocumentQuery()
                ->where(
                    'id',
                    $documentId
                );

        if (
            $this->documentColumnExists(
                'school_partner_id'
            )
        ) {

            $query->where(
                'school_partner_id',
                $schoolId
            );
        }

        if ($ownerOnly) {

            $this->applyDocumentOwner(
                $query,
                (int) Auth::id()
            );
        }

        return $query->firstOrFail();
    }
}