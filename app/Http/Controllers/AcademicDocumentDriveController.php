<?php

namespace App\Http\Controllers;

use App\Models\AcademicDocument;
use App\Models\Mapel;
use App\Models\TeacherMapel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use Throwable;

class AcademicDocumentDriveController extends Controller
{
    private const VIEW_ROLES = [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ];

    /*
    |--------------------------------------------------------------------------
    | 4 FOLDER DOKUMEN
    |--------------------------------------------------------------------------
    |
    | Nilai document_type ini mengikuti dua controller akademik yang sudah ada:
    |
    | AcademicDocumentController:
    |   - bagan_analisis
    |   - prota_prosem
    |
    | AcademicDocumentWordController:
    |   - rppm_word
    |   - refleksi_guru
    |
    |--------------------------------------------------------------------------
    */

    private const FOLDERS = [
        'analisis' => [
            'label' => 'Analisis',
            'icon' => 'fa-file-excel',
            'color' => 'blue',
            'types' => ['bagan_analisis'],
        ],

        'prota' => [
            'label' => 'PROTA',
            'icon' => 'fa-file-excel',
            'color' => 'emerald',
            'types' => ['prota_prosem'],
        ],

        'rppm' => [
            'label' => 'RPPM',
            'icon' => 'fa-file-word',
            'color' => 'amber',
            'types' => ['rppm_word'],
        ],

        'refleksi' => [
            'label' => 'Refleksi',
            'icon' => 'fa-file-word',
            'color' => 'violet',
            'types' => ['refleksi_guru'],
        ],
    ];

    public function index(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeDrive();

        $schoolId = (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            404,
            'ID sekolah tidak valid.'
        );

        /*
        |--------------------------------------------------------------------------
        | Ambil semua MAPEL yang mempunyai guru aktif di sekolah ini.
        |--------------------------------------------------------------------------
        */
        $teacherMapelRows = $this->teacherMapelRows($schoolId);

        $mapelIds = $teacherMapelRows
            ->pluck('mapel_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $mapels = collect();

        if ($mapelIds->isNotEmpty()) {
            $mapels = Mapel::query()
                ->whereIn('id', $mapelIds)
                ->get()
                ->sortBy(fn ($mapel) => strtolower($this->mapelName($mapel)))
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Siapkan struktur guru per mapel.
        |--------------------------------------------------------------------------
        */
        $teacherIds = $teacherMapelRows
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = $this->userDirectory($teacherIds);

        $teachersByMapel = [];

        foreach ($teacherMapelRows->groupBy('mapel_id') as $mapelId => $rows) {
            $teachers = $rows
                ->map(function ($row) use ($users) {
                    $userId = (int) $row->user_id;

                    return [
                        'id' => $userId,
                        'name' => $users[$userId]['name'] ?? ('Guru #' . $userId),
                        'role' => $users[$userId]['role'] ?? 'Guru',
                    ];
                })
                ->unique('id')
                ->sortBy(fn ($teacher) => strtolower($teacher['name']))
                ->values();

            $teachersByMapel[(int) $mapelId] = $teachers;
        }

        return view(
            'features.lms.school-admin.registrasi-guru.academic-drive',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'mapels' => $mapels,
                'teachersByMapel' => $teachersByMapel,
                'folders' => self::FOLDERS,
                'currentRole' => $this->currentRole(),
                'isGuru' => $this->currentRole() === 'guru',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILES DALAM FOLDER
    |--------------------------------------------------------------------------
    */

    public function files(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $subjectId,
        int $teacherId,
        string $folder
    ): JsonResponse {
        $this->authorizeDrive();

        $this->validateFolder($folder);

        $schoolId = (int) $schoolId;
        $subjectId = (int) $subjectId;
        $teacherId = (int) $teacherId;

        $this->assertTeacherBelongsToSubject(
            $schoolId,
            $subjectId,
            $teacherId
        );

        $types = self::FOLDERS[$folder]['types'];

        $ownerColumn = $this->documentOwnerColumn();

        abort_unless(
            $ownerColumn,
            500,
            'Kolom pemilik dokumen tidak ditemukan pada academic_documents.'
        );

        $query = AcademicDocument::query()
            ->where('school_partner_id', $schoolId)
            ->where('subject_id', $subjectId)
            ->where($ownerColumn, $teacherId)
            ->whereIn('document_type', $types)
            ->latest('id');

        $documents = $query->get();

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'folder_label' => self::FOLDERS[$folder]['label'],
            'documents' => $documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title ?: $document->original_filename ?: 'Dokumen',
                    'original_filename' => $document->original_filename,
                    'document_type' => $document->document_type,
                    'file_size' => $this->formatBytes($document->file_size ?? null),
                    'file_url' => $this->fileUrl($document),
                    'created_at' => optional($document->created_at)->format('d/m/Y H:i'),
                    'updated_at' => optional($document->updated_at)->format('d/m/Y H:i'),
                ];
            })->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD SATU FILE
    |--------------------------------------------------------------------------
    */

    public function download(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId,
        int $documentId
    ) {
        $this->authorizeDrive();

        $document = $this->documentForSchool($schoolId, $documentId);

        abort_if(
            empty($document->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($document->file_path),
            404,
            'File dokumen tidak ditemukan di storage.'
        );

        $name = $document->original_filename
            ?: (($document->title ?: 'dokumen') . '.docx');

        return $disk->download(
            $document->file_path,
            $this->safeFilename($name)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD BANYAK FILE -> ZIP
    |--------------------------------------------------------------------------
    */

    public function downloadSelected(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ) {
        $this->authorizeDrive();

        $validated = $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['integer', 'distinct'],
        ]);

        $documents = AcademicDocument::query()
            ->where('school_partner_id', (int) $schoolId)
            ->whereIn('id', $validated['document_ids'])
            ->get();

        abort_if(
            $documents->isEmpty(),
            404,
            'Tidak ada dokumen yang dapat diunduh.'
        );

        $zipPath = storage_path(
            'app/academic-drive-' . Str::uuid() . '.zip'
        );

        $zip = new ZipArchive();

        abort_unless(
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true,
            500,
            'ZIP tidak dapat dibuat.'
        );

        $added = 0;
        $usedNames = [];

        foreach ($documents as $document) {
            if (
                empty($document->file_path) ||
                !Storage::disk('public')->exists($document->file_path)
            ) {
                continue;
            }

            $absolutePath = Storage::disk('public')->path(
                $document->file_path
            );

            $name = $document->original_filename
                ?: (($document->title ?: 'dokumen-' . $document->id) . '.docx');

            $name = $this->uniqueZipName(
                $this->safeFilename($name),
                $usedNames
            );

            $zip->addFile($absolutePath, $name);
            $usedNames[$name] = true;
            $added++;
        }

        $zip->close();

        abort_if(
            $added === 0,
            404,
            'File fisik dari dokumen yang dipilih tidak ditemukan.'
        );

        return response()
            ->download(
                $zipPath,
                'dokumen-akademik-' . now()->format('Ymd-His') . '.zip'
            )
            ->deleteFileAfterSend(true);
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS BANYAK / SATU FILE
    |--------------------------------------------------------------------------
    */

    public function destroySelected(
        Request $request,
        string $role,
        string $schoolName,
        int $schoolId
    ): JsonResponse {
        $this->authorizeDrive();

        $validated = $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['integer', 'distinct'],
        ]);

        $documents = AcademicDocument::query()
            ->where('school_partner_id', (int) $schoolId)
            ->whereIn('id', $validated['document_ids'])
            ->get();

        $deleted = 0;

        DB::transaction(function () use ($documents, &$deleted) {
            foreach ($documents as $document) {
                $this->deleteDocument($document);
                $deleted++;
            }
        });

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => $deleted . ' dokumen berhasil dihapus.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function authorizeDrive(): void
    {
        abort_unless(
            Auth::check(),
            401,
            'Pengguna belum login.'
        );

        abort_unless(
            in_array($this->currentRole(), self::VIEW_ROLES, true),
            403,
            'Anda tidak memiliki akses ke Academic Drive.'
        );
    }

    private function currentRole(): string
    {
        return strtolower(
            trim(
                (string) (Auth::user()?->role ?? '')
            )
        );
    }

    private function validateFolder(string $folder): void
    {
        abort_unless(
            array_key_exists($folder, self::FOLDERS),
            404,
            'Folder dokumen tidak ditemukan.'
        );
    }

    private function teacherMapelRows(int $schoolId)
    {
        /*
         * teacher_mapels adalah sumber hubungan GURU <-> MAPEL.
         *
         * Struktur database project:
         * teacher_mapels.school_class_id -> school_classes.id
         * school_classes.school_partner_id -> sekolah
         *
         * JANGAN memakai fallback tanpa filter sekolah. Kalau relasi gagal,
         * data dari sekolah lain tidak boleh ikut masuk ke Academic Drive.
         */
        $query = TeacherMapel::query()
            ->where('teacher_mapels.is_active', true)
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_classes.school_partner_id', $schoolId);
            });

        return $query
            ->select([
                'teacher_mapels.id',
                'teacher_mapels.user_id',
                'teacher_mapels.mapel_id',
                'teacher_mapels.school_class_id',
                'teacher_mapels.is_active',
            ])
            ->get();
    }

    private function assertTeacherBelongsToSubject(
        int $schoolId,
        int $subjectId,
        int $teacherId
    ): void {
        $rows = $this->teacherMapelRows($schoolId);

        abort_unless(
            $rows->contains(function ($row) use ($subjectId, $teacherId) {
                return
                    (int) $row->mapel_id === $subjectId &&
                    (int) $row->user_id === $teacherId;
            }),
            404,
            'Guru tidak terdaftar pada mata pelajaran tersebut.'
        );
    }

    private function userDirectory($ids): array
    {
        $ids = collect($ids)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (
            $ids->isEmpty() ||
            !Schema::hasTable('user_accounts')
        ) {
            return [];
        }

        $rows = DB::table('user_accounts')
            ->whereIn('id', $ids)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $name = null;

            foreach ([
                'name',
                'nama',
                'nama_lengkap',
                'full_name',
                'display_name',
                'username',
                'email',
            ] as $column) {
                if (
                    Schema::hasColumn('user_accounts', $column) &&
                    !empty($row->{$column})
                ) {
                    $name = trim((string) $row->{$column});
                    break;
                }
            }

            $result[(int) $row->id] = [
                'name' => $name ?: 'Guru #' . $row->id,
                'role' => Schema::hasColumn('user_accounts', 'role')
                    ? ((string) ($row->role ?? 'Guru'))
                    : 'Guru',
            ];
        }

        return $result;
    }

    private function mapelName($mapel): string
    {
        foreach ([
            'mata_pelajaran',
            'nama_mapel',
            'nama',
            'name',
        ] as $column) {
            if (
                Schema::hasColumn($mapel->getTable(), $column) &&
                !empty($mapel->{$column})
            ) {
                return trim((string) $mapel->{$column});
            }
        }

        return 'Mata Pelajaran #' . $mapel->id;
    }

    private function documentOwnerColumn(): ?string
    {
        foreach ([
            'owner_user_id',
            'user_id',
            'created_by',
            'uploaded_by',
        ] as $column) {
            if (Schema::hasColumn('academic_documents', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function documentForSchool(
        int $schoolId,
        int $documentId
    ): AcademicDocument {
        return AcademicDocument::query()
            ->where('id', $documentId)
            ->where('school_partner_id', $schoolId)
            ->firstOrFail();
    }

    private function fileUrl($document): ?string
    {
        if (
            empty($document->file_path) ||
            !Storage::disk('public')->exists($document->file_path)
        ) {
            return null;
        }

        return Storage::disk('public')->url(
            $document->file_path
        );
    }

    private function deleteDocument(
        AcademicDocument $document
    ): void {
        /*
         * Hapus file fisik terlebih dahulu.
         */
        if (
            !empty($document->file_path) &&
            Storage::disk('public')->exists($document->file_path)
        ) {
            Storage::disk('public')->delete(
                $document->file_path
            );
        }

        /*
         * Hapus komentar/cell/sheet terkait bila ada.
         */
        if (
            Schema::hasTable('academic_document_sheets')
        ) {
            $sheetIds = DB::table('academic_document_sheets')
                ->where('academic_document_id', $document->id)
                ->pluck('id');

            if ($sheetIds->isNotEmpty()) {
                if (
                    Schema::hasTable('academic_document_comments') &&
                    Schema::hasColumn(
                        'academic_document_comments',
                        'academic_document_sheet_id'
                    )
                ) {
                    DB::table('academic_document_comments')
                        ->whereIn(
                            'academic_document_sheet_id',
                            $sheetIds
                        )
                        ->delete();
                }

                if (
                    Schema::hasTable('academic_document_cells')
                ) {
                    DB::table('academic_document_cells')
                        ->whereIn(
                            'academic_document_sheet_id',
                            $sheetIds
                        )
                        ->delete();
                }
            }

            DB::table('academic_document_sheets')
                ->where(
                    'academic_document_id',
                    $document->id
                )
                ->delete();
        }

        /*
         * RPPM / Refleksi menyimpan editor content dan comments
         * pada tabel terpisah. Hapus bila tabelnya tersedia.
         */
        if (
            Schema::hasTable('academic_document_word_comments')
        ) {
            DB::table('academic_document_word_comments')
                ->where(
                    'academic_document_id',
                    $document->id
                )
                ->delete();
        }

        if (
            Schema::hasTable('academic_document_word_contents')
        ) {
            DB::table('academic_document_word_contents')
                ->where(
                    'academic_document_id',
                    $document->id
                )
                ->delete();
        }

        $document->delete();
    }

    private function safeFilename(string $filename): string
    {
        $filename = trim($filename);

        if ($filename === '') {
            $filename = 'dokumen';
        }

        return Str::of($filename)
            ->replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-')
            ->toString();
    }

    private function uniqueZipName(
        string $name,
        array $used
    ): string {
        if (!isset($used[$name])) {
            return $name;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        $counter = 2;

        do {
            $candidate = $base . ' (' . $counter . ')';

            if ($extension !== '') {
                $candidate .= '.' . $extension;
            }

            $counter++;
        } while (isset($used[$candidate]));

        return $candidate;
    }

    private function formatBytes($bytes): string
    {
        if (!$bytes || !is_numeric($bytes)) {
            return '-';
        }

        $bytes = (float) $bytes;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return number_format($value, 1, ',', '.') . ' ' . $units[$unit];
    }
}
