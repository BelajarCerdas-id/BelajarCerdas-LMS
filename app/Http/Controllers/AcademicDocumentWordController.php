<?php

namespace App\Http\Controllers;

use App\Models\AcademicDocument;
use App\Models\AcademicDocumentWordComment;
use App\Models\AcademicDocumentWordContent;
use App\Models\SchoolStaffProfile;
use App\Models\TeacherMapel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AcademicDocumentWordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

    private const VIEW_ROLES = [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ];

    private const GURU_ROLE = 'guru';


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT TYPE
    |--------------------------------------------------------------------------
    */

    private const RPPM_TYPE = 'rppm_word';

    private const REFLEKSI_TYPE = 'refleksi_guru';

    private const ANALISIS_RPPM_TYPE = 'analisis_rppm_word';


    /*
    |--------------------------------------------------------------------------
    | STORAGE DIRECTORY
    |--------------------------------------------------------------------------
    */

    private const RPPM_DIRECTORY = 'rppm-word';

    private const REFLEKSI_DIRECTORY = 'refleksi-guru';

    private const ANALISIS_RPPM_DIRECTORY = 'analisis-rppm';


    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

    private const RPPM_VIEW =
        'features.lms.school-admin.registrasi-guru.tampilan.tamp-RPPM';

    private const REFLEKSI_VIEW =
        'features.lms.school-admin.registrasi-guru.tampilan.tamp-refleksi-guru';


    /*
    |--------------------------------------------------------------------------
    | ROLE HELPER
    |--------------------------------------------------------------------------
    */

    private function currentRole(): string
    {
        return strtolower(
            trim(
                (string) (
                    Auth::user()?->role
                    ?? ''
                )
            )
        );
    }


    private function isGuru(): bool
    {
        return $this->currentRole() === self::GURU_ROLE;
    }


    private function abortIfCannotView(): void
    {
        abort_unless(
            in_array(
                $this->currentRole(),
                self::VIEW_ROLES,
                true
            ),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SCHOOL PROFILE
    |--------------------------------------------------------------------------
    */

    private function getSchoolProfileOrFail(
        $user,
        int $schoolId
    ): SchoolStaffProfile {
        return SchoolStaffProfile::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'school_partner_id',
                $schoolId
            )
            ->firstOrFail();
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT OWNER COLUMN
    |--------------------------------------------------------------------------
    */

    private function documentOwnerColumn(): ?string
    {
        static $ownerColumn = false;

        if ($ownerColumn !== false) {
            return $ownerColumn;
        }

        $possibleColumns = [
            'owner_user_id',
            'user_id',
            'created_by',
            'uploaded_by',
        ];

        foreach ($possibleColumns as $column) {
            if (
                Schema::hasColumn(
                    'academic_documents',
                    $column
                )
            ) {
                $ownerColumn = $column;

                return $ownerColumn;
            }
        }

        $ownerColumn = null;

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GURU OWNER FILTER
    |--------------------------------------------------------------------------
    */

    private function applyGuruOwnerFilter(
        $query,
        int $userId
    ) {
        $ownerColumn = $this->documentOwnerColumn();

        abort_unless(
            $ownerColumn,
            500,
            'Kolom pemilik dokumen belum tersedia pada tabel academic_documents.'
        );

        return $query->where(
            $ownerColumn,
            $userId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SET DOCUMENT OWNER
    |--------------------------------------------------------------------------
    */

    private function setDocumentOwner(
        AcademicDocument $document,
        int $userId
    ): void {
        $ownerColumn = $this->documentOwnerColumn();

        if ($ownerColumn) {
            $document->{$ownerColumn} = $userId;

            return;
        }

        if ($this->isGuru()) {
            throw new \RuntimeException(
                'Kolom pemilik dokumen belum tersedia pada tabel academic_documents.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DOCUMENT OWNER
    |--------------------------------------------------------------------------
    */

    private function ensureGuruOwnsDocument(
        AcademicDocument $document,
        $user
    ): void {
        if (!$this->isGuru()) {
            return;
        }

        $ownerColumn = $this->documentOwnerColumn();

        abort_unless(
            $ownerColumn,
            500,
            'Kolom pemilik dokumen belum tersedia pada tabel academic_documents.'
        );

        $ownerId = $document->{$ownerColumn};

        abort_unless(
            $ownerId !== null
            && (int) $ownerId === (int) $user->id,
            403,
            'Anda tidak memiliki akses ke dokumen ini.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT TYPE CHECK
    |--------------------------------------------------------------------------
    */

    private function documentTypeMatches(
        ?string $actualType,
        string $expectedType
    ): bool {
        return strtolower(
            trim(
                (string) $actualType
            )
        ) === strtolower(
            trim(
                $expectedType
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function getTypedDocumentOrFail(
        int $documentId,
        int $schoolId,
        string $documentType
    ): AcademicDocument {
        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        /*
        |----------------------------------------------------------------------
        | Pastikan user merupakan anggota sekolah.
        |----------------------------------------------------------------------
        */

        $this->getSchoolProfileOrFail(
            $user,
            $schoolId
        );

        /*
        |----------------------------------------------------------------------
        | Ambil dokumen.
        |----------------------------------------------------------------------
        */

        $document = AcademicDocument::query()
            ->where(
                'id',
                $documentId
            )
            ->where(
                'school_partner_id',
                $schoolId
            )
            ->whereRaw(
                'LOWER(TRIM(document_type)) = ?',
                [
                    strtolower(
                        trim(
                            $documentType
                        )
                    ),
                ]
            )
            ->first();

        abort_if(
            !$document,
            404,
            'Dokumen tidak ditemukan pada fitur ini.'
        );

        /*
        |----------------------------------------------------------------------
        | Guru hanya boleh dokumen sendiri.
        |----------------------------------------------------------------------
        */

        $this->ensureGuruOwnsDocument(
            $document,
            $user
        );

        return $document;
    }


    /*
    |--------------------------------------------------------------------------
    | GET LATEST TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function getLatestTypedDocument(
        int $schoolId,
        string $documentType,
        $user
    ): ?AcademicDocument {
        $query = AcademicDocument::query()
            ->where(
                'school_partner_id',
                $schoolId
            )
            ->whereRaw(
                'LOWER(TRIM(document_type)) = ?',
                [
                    strtolower(
                        trim(
                            $documentType
                        )
                    ),
                ]
            );

        if ($this->isGuru()) {
            $this->applyGuruOwnerFilter(
                $query,
                (int) $user->id
            );
        }

        return $query
            ->latest('id')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | GET TEACHER MAPEL
    |--------------------------------------------------------------------------
    |
    | Mapel tidak dipilih manual.
    | Sistem mengambil mapel aktif milik Guru.
    |--------------------------------------------------------------------------
    */

    private function getTeacherMapelOrFail(
        $user,
        int $schoolId
    ): TeacherMapel {
        $teacherMapel = TeacherMapel::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'is_active',
                1
            )
            ->whereHas(
                'SchoolClass',
                function ($query) use ($schoolId) {
                    $query->where(
                        'school_partner_id',
                        $schoolId
                    );
                }
            )
            ->first();

        abort_if(
            !$teacherMapel,
            422,
            'Guru belum memiliki mata pelajaran yang terdaftar pada sekolah ini.'
        );

        abort_if(
            !$teacherMapel->mapel_id,
            422,
            'Mata pelajaran Guru belum memiliki ID yang valid.'
        );

        return $teacherMapel;
    }


    /*
    |--------------------------------------------------------------------------
    | STORE WORD UPLOAD
    |--------------------------------------------------------------------------
    */

    private function storeWordUpload(
        Request $request,
        $user,
        SchoolStaffProfile $profile,
        int $subjectId,
        string $documentType,
        string $directory,
        string $successMessage
    ): JsonResponse {
        /*
        |----------------------------------------------------------------------
        | VALIDATION
        |----------------------------------------------------------------------
        */

        $request->validate(
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:docx',
                    'max:20480',
                ],
            ],
            [
                'file.required' =>
                    'Silakan pilih file Word.',

                'file.file' =>
                    'File tidak valid.',

                'file.mimes' =>
                    'File harus berformat DOCX.',

                'file.max' =>
                    'Ukuran file maksimal 20 MB.',
            ]
        );

        $file = $request->file('file');

        if (
            !$file
            || !$file->isValid()
        ) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'File Word tidak valid.',
                ],
                422
            );
        }

        /*
        |----------------------------------------------------------------------
        | FILE INFO
        |----------------------------------------------------------------------
        */

        $originalFilename =
            $file->getClientOriginalName();

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );

        /*
        |----------------------------------------------------------------------
        | TITLE
        |----------------------------------------------------------------------
        */

        $title =
            trim(
                pathinfo(
                    $originalFilename,
                    PATHINFO_FILENAME
                )
            );

        if ($title === '') {
            $title = 'Dokumen Word';
        }

        /*
        |----------------------------------------------------------------------
        | SAFE FILENAME
        |----------------------------------------------------------------------
        */

        $safeFilename =
            preg_replace(
                '/[^A-Za-z0-9._-]/',
                '_',
                $originalFilename
            );

        $filename =
            time()
            . '_'
            . uniqid()
            . '_'
            . $safeFilename;

        $filePath = null;

        try {
            /*
            |------------------------------------------------------------------
            | STORE FILE
            |------------------------------------------------------------------
            */

            $filePath =
                Storage::disk('public')
                    ->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );

            if (!$filePath) {
                throw new \RuntimeException(
                    'File gagal disimpan ke storage.'
                );
            }

            /*
            |------------------------------------------------------------------
            | DATABASE TRANSACTION
            |------------------------------------------------------------------
            */

            $document = DB::transaction(
                function () use (
                    $user,
                    $profile,
                    $subjectId,
                    $title,
                    $originalFilename,
                    $filePath,
                    $extension,
                    $documentType,
                    $file
                ) {
                    $document =
                        new AcademicDocument();

                    /*
                    |----------------------------------------------------------
                    | SCHOOL
                    |----------------------------------------------------------
                    */

                    $document->school_partner_id =
                        (int) $profile->school_partner_id;

                    /*
                    |----------------------------------------------------------
                    | OWNER
                    |----------------------------------------------------------
                    */

                    $this->setDocumentOwner(
                        $document,
                        (int) $user->id
                    );

                    /*
                    |----------------------------------------------------------
                    | SUBJECT
                    |----------------------------------------------------------
                    */

                    $document->subject_id =
                        (int) $subjectId;

                    /*
                    |----------------------------------------------------------
                    | BASIC
                    |----------------------------------------------------------
                    */

                    $document->title =
                        $title;

                    $document->original_filename =
                        $originalFilename;

                    $document->file_path =
                        $filePath;

                    /*
                    |----------------------------------------------------------
                    | TYPE
                    |----------------------------------------------------------
                    */

                    $document->document_type =
                        $documentType;

                    /*
                    |----------------------------------------------------------
                    | OPTIONAL FILE TYPE
                    |----------------------------------------------------------
                    */

                    if (
                        Schema::hasColumn(
                            'academic_documents',
                            'file_type'
                        )
                    ) {
                        $document->file_type =
                            $extension;
                    }

                    /*
                    |----------------------------------------------------------
                    | MIME
                    |----------------------------------------------------------
                    */

                    if (
                        Schema::hasColumn(
                            'academic_documents',
                            'mime_type'
                        )
                    ) {
                        $document->mime_type =
                            $file->getMimeType();
                    }

                    /*
                    |----------------------------------------------------------
                    | FILE SIZE
                    |----------------------------------------------------------
                    */

                    if (
                        Schema::hasColumn(
                            'academic_documents',
                            'file_size'
                        )
                    ) {
                        $document->file_size =
                            $file->getSize();
                    }

                    /*
                    |----------------------------------------------------------
                    | STATUS
                    |----------------------------------------------------------
                    */

                    if (
                        Schema::hasColumn(
                            'academic_documents',
                            'status'
                        )
                    ) {
                        $document->status =
                            'draft';
                    }

                    /*
                    |----------------------------------------------------------
                    | SAVE
                    |----------------------------------------------------------
                    */

                    $document->save();

                    /*
                    |----------------------------------------------------------
                    | WORD CONTENT
                    |----------------------------------------------------------
                    */

                    AcademicDocumentWordContent::updateOrCreate(
                        [
                            'academic_document_id' =>
                                $document->id,
                        ],
                        [
                            'title' =>
                                $title,

                            'content' =>
                                null,

                            'last_saved_at' =>
                                now(),
                        ]
                    );

                    return $document;
                }
            );

            /*
            |------------------------------------------------------------------
            | FILE URL
            |------------------------------------------------------------------
            */

            $fileUrl =
                Storage::disk('public')
                    ->url(
                        $filePath
                    );

            /*
            |------------------------------------------------------------------
            | RESPONSE
            |------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        $successMessage,

                    'document_id' =>
                        $document->id,

                    'title' =>
                        $document->title,

                    'original_filename' =>
                        $document->original_filename,

                    'document_type' =>
                        $document->document_type,

                    'file_path' =>
                        $document->file_path,

                    'file_url' =>
                        $fileUrl,

                    'subject_id' =>
                        $subjectId,
                ],
                200
            );

        } catch (Throwable $e) {
            /*
            |------------------------------------------------------------------
            | DELETE FILE WHEN DB FAILS
            |------------------------------------------------------------------
            */

            if (
                $filePath
                && Storage::disk('public')
                    ->exists(
                        $filePath
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $filePath
                    );
            }

            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Upload gagal: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - INDEX / TAMPILAN
    |--------------------------------------------------------------------------
    |
    | INI METHOD YANG SEBELUMNYA HILANG.
    |
    | Ketika user membuka:
    |
    | /rppm/tampilan
    |
    | controller akan mengambil dokumen RPPM terakhir dari database.
    |--------------------------------------------------------------------------
    */

    public function rppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ) {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId = (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        /*
        |----------------------------------------------------------------------
        | PROFILE
        |----------------------------------------------------------------------
        */

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        /*
        |----------------------------------------------------------------------
        | AMBIL RPPM TERAKHIR
        |----------------------------------------------------------------------
        */

        $document =
            $this->getLatestTypedDocument(
                (int) $profile->school_partner_id,
                self::RPPM_TYPE,
                $user
            );

        /*
        |----------------------------------------------------------------------
        | WORD CONTENT
        |----------------------------------------------------------------------
        */

        $wordContent = null;

        if ($document) {
            $wordContent =
                AcademicDocumentWordContent::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->latest('id')
                    ->first();
        }

        /*
        |----------------------------------------------------------------------
        | COMMENTS
        |----------------------------------------------------------------------
        */

        $comments = collect();

        if ($document) {
            $comments =
                AcademicDocumentWordComment::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->whereNull('parent_id')
                    ->with(
                        [
                            'user',
                            'replies.user',
                        ]
                    )
                    ->latest('id')
                    ->get();
        }

        /*
        |----------------------------------------------------------------------
        | FILE URL
        |----------------------------------------------------------------------
        */

        $fileUrl = null;

        if (
            $document
            && !empty($document->file_path)
            && Storage::disk('public')
                ->exists(
                    $document->file_path
                )
        ) {
            $fileUrl =
                Storage::disk('public')
                    ->url(
                        $document->file_path
                    );
        }

        /*
        |----------------------------------------------------------------------
        | VIEW
        |----------------------------------------------------------------------
        */

        return view(
            self::RPPM_VIEW,
            [
                'document' =>
                    $document,

                'documentId' =>
                    $document?->id,

                'wordContent' =>
                    $wordContent,

                'comments' =>
                    $comments,

                'fileUrl' =>
                    $fileUrl,

                'isGuru' =>
                    $this->isGuru(),

                'currentRole' =>
                    $this->currentRole(),

                'role' =>
                    $role,

                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'user' =>
                    $user,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - UPLOAD
    |--------------------------------------------------------------------------
    */

    public function upload(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ): JsonResponse {
        if (!$this->isGuru()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Hanya Guru yang dapat mengunggah dokumen RPPM.',
                ],
                403
            );
        }

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId = (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        $teacherMapel =
            $this->getTeacherMapelOrFail(
                $user,
                $schoolId
            );

        $subjectId =
            (int) $teacherMapel->mapel_id;

        $response =
            $this->storeWordUpload(
                $request,
                $user,
                $profile,
                $subjectId,
                self::RPPM_TYPE,
                self::RPPM_DIRECTORY,
                'Dokumen RPPM berhasil diunggah.'
            );

        if (
            $response instanceof JsonResponse
            && $response->getStatusCode() === 200
        ) {
            $data =
                $response->getData(true);

            if (
                !empty($data['success'])
                && !empty($data['document_id'])
            ) {
                $data['edit_url'] =
                    route(
                        'lms.schoolAdmin.registrasiGuru.rppm.word.edit',
                        [
                            'role' =>
                                $role,

                            'schoolName' =>
                                $schoolName,

                            'schoolId' =>
                                $schoolId,

                            'documentId' =>
                                $data['document_id'],
                        ]
                    );

                return response()->json(
                    $data,
                    200
                );
            }
        }

        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                self::RPPM_TYPE
            );

        return $this->renderWordEditor(
            $document,
            $user,
            $role,
            $schoolName,
            $schoolId,
            self::RPPM_VIEW
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - SAVE
    |--------------------------------------------------------------------------
    */

    public function save(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->saveTypedDocument(
            $request,
            $schoolId,
            $documentId,
            self::RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - DOWNLOAD
    |--------------------------------------------------------------------------
    */

    public function download(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        return $this->downloadTypedDocument(
            $schoolId,
            $documentId,
            self::RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->destroyTypedDocument(
            $schoolId,
            $documentId,
            self::RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI GURU - INDEX
    |--------------------------------------------------------------------------
    */

    public function refleksiGuru(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ) {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        $document =
            $this->getLatestTypedDocument(
                (int) $profile->school_partner_id,
                self::REFLEKSI_TYPE,
                $user
            );

        $wordContent = null;

        if ($document) {
            $wordContent =
                AcademicDocumentWordContent::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->latest('id')
                    ->first();
        }

        $comments = collect();

        if ($document) {
            $comments =
                AcademicDocumentWordComment::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->whereNull('parent_id')
                    ->with(
                        [
                            'user',
                            'replies.user',
                        ]
                    )
                    ->latest('id')
                    ->get();
        }

        $fileUrl = null;

        if (
            $document
            && !empty($document->file_path)
            && Storage::disk('public')
                ->exists(
                    $document->file_path
                )
        ) {
            $fileUrl =
                Storage::disk('public')
                    ->url(
                        $document->file_path
                    );
        }

        return view(
            self::REFLEKSI_VIEW,
            [
                'document' =>
                    $document,

                'documentId' =>
                    $document?->id,

                'wordContent' =>
                    $wordContent,

                'comments' =>
                    $comments,

                'fileUrl' =>
                    $fileUrl,

                'isGuru' =>
                    $this->isGuru(),

                'currentRole' =>
                    $this->currentRole(),

                'role' =>
                    $role,

                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'user' =>
                    $user,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI GURU - UPLOAD
    |--------------------------------------------------------------------------
    */

    public function uploadRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ): JsonResponse {
        if (!$this->isGuru()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Hanya Guru yang dapat mengunggah dokumen Refleksi Guru.',
                ],
                403
            );
        }

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        $teacherMapel =
            $this->getTeacherMapelOrFail(
                $user,
                $schoolId
            );

        $subjectId =
            (int) $teacherMapel->mapel_id;

        $response =
            $this->storeWordUpload(
                $request,
                $user,
                $profile,
                $subjectId,
                self::REFLEKSI_TYPE,
                self::REFLEKSI_DIRECTORY,
                'Dokumen Refleksi Guru berhasil diunggah.'
            );

        if (
            $response instanceof JsonResponse
            && $response->getStatusCode() === 200
        ) {
            $data =
                $response->getData(true);

            if (
                !empty($data['success'])
                && !empty($data['document_id'])
            ) {
                $data['edit_url'] =
                    route(
                        'lms.schoolAdmin.registrasiGuru.refleksi-guru.edit',
                        [
                            'role' =>
                                $role,

                            'schoolName' =>
                                $schoolName,

                            'schoolId' =>
                                $schoolId,

                            'documentId' =>
                                $data['document_id'],
                        ]
                    );

                return response()->json(
                    $data,
                    200
                );
            }
        }

        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - EDIT
    |--------------------------------------------------------------------------
    */

    public function editRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                self::REFLEKSI_TYPE
            );

        return $this->renderWordEditor(
            $document,
            $user,
            $role,
            $schoolName,
            $schoolId,
            self::REFLEKSI_VIEW
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - SAVE
    |--------------------------------------------------------------------------
    */

    public function saveRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->saveTypedDocument(
            $request,
            $schoolId,
            $documentId,
            self::REFLEKSI_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - DOWNLOAD
    |--------------------------------------------------------------------------
    */

    public function downloadRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        return $this->downloadTypedDocument(
            $schoolId,
            $documentId,
            self::REFLEKSI_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - DELETE
    |--------------------------------------------------------------------------
    */

    public function destroyRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->destroyTypedDocument(
            $schoolId,
            $documentId,
            self::REFLEKSI_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER WORD EDITOR
    |--------------------------------------------------------------------------
    */

    private function renderWordEditor(
        AcademicDocument $document,
        $user,
        string $role,
        string $schoolName,
        int $schoolId,
        string $view
    ) {
        /*
        |----------------------------------------------------------------------
        | WORD CONTENT
        |----------------------------------------------------------------------
        */

        $wordContent =
            AcademicDocumentWordContent::query()
                ->where(
                    'academic_document_id',
                    $document->id
                )
                ->latest('id')
                ->first();

        /*
        |----------------------------------------------------------------------
        | COMMENTS
        |----------------------------------------------------------------------
        */

        $comments =
            AcademicDocumentWordComment::query()
                ->where(
                    'academic_document_id',
                    $document->id
                )
                ->whereNull('parent_id')
                ->with(
                    [
                        'user',
                        'replies.user',
                    ]
                )
                ->latest('id')
                ->get();

        /*
        |----------------------------------------------------------------------
        | FILE URL
        |----------------------------------------------------------------------
        */

        $fileUrl = null;

        if (
            !empty($document->file_path)
            && Storage::disk('public')
                ->exists(
                    $document->file_path
                )
        ) {
            $fileUrl =
                Storage::disk('public')
                    ->url(
                        $document->file_path
                    );
        }

        /*
        |----------------------------------------------------------------------
        | VIEW
        |----------------------------------------------------------------------
        */

        return view(
            $view,
            [
                'document' =>
                    $document,

                'documentId' =>
                    $document->id,

                'wordContent' =>
                    $wordContent,

                'comments' =>
                    $comments,

                'isGuru' =>
                    $this->isGuru(),

                'currentRole' =>
                    $this->currentRole(),

                'role' =>
                    $role,

                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'user' =>
                    $user,

                'fileUrl' =>
                    $fileUrl,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function saveTypedDocument(
        Request $request,
        string $schoolId,
        string $documentId,
        string $documentType
    ): JsonResponse {
        $this->abortIfCannotView();

        if (!$this->isGuru()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Hanya Guru yang dapat mengedit dokumen.',
                ],
                403
            );
        }

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                $documentType
            );

        /*
        |----------------------------------------------------------------------
        | VALIDATION
        |----------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'title' =>
                        [
                            'nullable',
                            'string',
                            'max:255',
                        ],

                    'content' =>
                        [
                            'nullable',
                            'string',
                        ],
                ]
            );

        $savedAt =
            now();

        /*
        |----------------------------------------------------------------------
        | SAVE
        |----------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $document,
                $validated,
                $savedAt
            ) {
                $existing =
                    AcademicDocumentWordContent::query()
                        ->where(
                            'academic_document_id',
                            $document->id
                        )
                        ->latest('id')
                        ->first();

                /*
                |--------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------
                */

                $title =
                    array_key_exists(
                        'title',
                        $validated
                    )
                        ? (
                            trim(
                                (string)
                                ($validated['title'] ?? '')
                            )
                            ?: (
                                $existing?->title
                                ?: $document->title
                            )
                        )
                        : (
                            $existing?->title
                            ?: $document->title
                        );

                /*
                |--------------------------------------------------------------
                | CONTENT
                |--------------------------------------------------------------
                |
                | Jika content tidak dikirim, content lama dipertahankan.
                | Ini penting untuk autosave.
                |
                */

                $content =
                    array_key_exists(
                        'content',
                        $validated
                    )
                        ? $validated['content']
                        : (
                            $existing?->content
                            ?? null
                        );

                AcademicDocumentWordContent::updateOrCreate(
                    [
                        'academic_document_id' =>
                            $document->id,
                    ],
                    [
                        'title' =>
                            $title,

                        'content' =>
                            $content,

                        'last_saved_at' =>
                            $savedAt,
                    ]
                );

                /*
                |--------------------------------------------------------------
                | UPDATE DOCUMENT TITLE
                |--------------------------------------------------------------
                */

                if (
                    $title !== ''
                    && $title !== $document->title
                ) {
                    $document->update(
                        [
                            'title' =>
                                $title,
                        ]
                    );
                }
            }
        );

        return response()->json(
            [
                'success' =>
                    true,

                'message' =>
                    'Dokumen berhasil disimpan.',

                'saved_at' =>
                    $savedAt->format(
                        'Y-m-d H:i:s'
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function downloadTypedDocument(
        string $schoolId,
        string $documentId,
        string $documentType
    ) {
        $this->abortIfCannotView();

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                $documentType
            );

        abort_if(
            empty($document->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        $disk =
            Storage::disk('public');

        abort_unless(
            $disk->exists(
                $document->file_path
            ),
            404,
            'File dokumen tidak ditemukan di storage.'
        );

        $downloadName =
            $document->original_filename
            ?: (
                ($document->title ?: 'dokumen')
                . '.docx'
            );

        return $disk->download(
            $document->file_path,
            $downloadName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function destroyTypedDocument(
        string $schoolId,
        string $documentId,
        string $documentType
    ): JsonResponse {
        if (!$this->isGuru()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Hanya Guru yang dapat menghapus dokumen.',
                ],
                403
            );
        }

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                $documentType
            );

        try {
            DB::transaction(
                function () use ($document) {
                    /*
                    |----------------------------------------------------------
                    | COMMENTS
                    |----------------------------------------------------------
                    */

                    AcademicDocumentWordComment::query()
                        ->where(
                            'academic_document_id',
                            $document->id
                        )
                        ->delete();

                    /*
                    |----------------------------------------------------------
                    | WORD CONTENT
                    |----------------------------------------------------------
                    */

                    AcademicDocumentWordContent::query()
                        ->where(
                            'academic_document_id',
                            $document->id
                        )
                        ->delete();

                    /*
                    |----------------------------------------------------------
                    | FILE
                    |----------------------------------------------------------
                    */

                    if (
                        !empty(
                            $document->file_path
                        )
                        && Storage::disk('public')
                            ->exists(
                                $document->file_path
                            )
                    ) {
                        Storage::disk('public')
                            ->delete(
                                $document->file_path
                            );
                    }

                    /*
                    |----------------------------------------------------------
                    | DOCUMENT
                    |----------------------------------------------------------
                    */

                    $document->delete();
                }
            );

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Dokumen berhasil dihapus.',
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Gagal menghapus dokumen: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - COMMENT
    |--------------------------------------------------------------------------
    */

    public function storeComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->storeTypedComment(
            $request,
            $schoolId,
            $documentId,
            self::RPPM_TYPE
        );
    }


    public function replyComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->replyTypedComment(
            $request,
            $schoolId,
            $commentId,
            self::RPPM_TYPE
        );
    }


    public function resolveComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->resolveTypedComment(
            $schoolId,
            $commentId,
            self::RPPM_TYPE
        );
    }


    public function destroyComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->destroyTypedComment(
            $schoolId,
            $commentId,
            self::RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - COMMENT
    |--------------------------------------------------------------------------
    |
    | Nama method DISESUAIKAN dengan routes/web.php:
    |
    | storeRefleksiComment
    | replyRefleksiComment
    | resolveRefleksiComment
    | destroyRefleksiComment
    |--------------------------------------------------------------------------
    */

    public function storeRefleksiComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->storeTypedComment(
            $request,
            $schoolId,
            $documentId,
            self::REFLEKSI_TYPE
        );
    }


    public function replyRefleksiComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->replyTypedComment(
            $request,
            $schoolId,
            $commentId,
            self::REFLEKSI_TYPE
        );
    }


    public function resolveRefleksiComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->resolveTypedComment(
            $schoolId,
            $commentId,
            self::REFLEKSI_TYPE
        );
    }


    public function destroyRefleksiComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->destroyTypedComment(
            $schoolId,
            $commentId,
            self::REFLEKSI_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BACKWARD COMPATIBILITY
    |--------------------------------------------------------------------------
    |
    | Method lama tetap disediakan agar Blade lama tidak langsung error.
    |--------------------------------------------------------------------------
    */

    public function storeCommentRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->storeRefleksiComment(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $documentId
        );
    }


    public function replyCommentRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->replyRefleksiComment(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $commentId
        );
    }


    public function resolveCommentRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->resolveRefleksiComment(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $commentId
        );
    }


    public function destroyCommentRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->destroyRefleksiComment(
            $request,
            $role,
            $schoolName,
            $schoolId,
            $commentId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE COMMENT USER ID
    |--------------------------------------------------------------------------
    |
    | academic_document_word_comments.user_id memiliki FK ke users.id.
    | Pada beberapa kondisi Auth::id() dapat berbeda dari primary key users
    | (misalnya session lama / provider auth berbeda). Jangan memasukkan ID
    | yang tidak ada ke tabel komentar.
    |
    | Urutan pencarian:
    | 1. Gunakan Auth ID jika memang ada di users.
    | 2. Jika tidak ada, cocokkan identitas login (email/username/login)
    |    yang juga tersedia pada tabel users.
    | 3. Jika tidak ditemukan, hentikan dengan pesan yang jelas.
    |--------------------------------------------------------------------------
    */

    private function resolveCommentUserId($user): int
    {
        /*
        |----------------------------------------------------------------------
        | CATATAN ARSITEKTUR AUTH
        |----------------------------------------------------------------------
        | Project ini menggunakan user_accounts sebagai sumber login, sedangkan
        | academic_document_word_comments.user_id masih memiliki FK ke users.id.
        |
        | Akibatnya Auth::id() (contoh: 170) tidak selalu tersedia di users.
        | Tabel users pada database lama bahkan dapat kosong.
        |
        | Controller ini memakai compatibility bridge:
        | - jika users.id sudah ada -> gunakan ID tersebut;
        | - jika users.email sudah ada -> gunakan ID tersebut;
        | - jika belum ada -> buat shadow user di users dengan ID akun login.
        |
        | Dengan begitu FK tetap valid tanpa menghapus FK dan tanpa mengubah
        | user_id menjadi NULL.
        |----------------------------------------------------------------------
        */

        abort_unless(
            Schema::hasTable('users'),
            500,
            'Tabel users tidak tersedia, sedangkan tabel komentar masih menggunakan FK ke users.id.'
        );

        $authId = (int) $user->getAuthIdentifier();

        abort_unless(
            $authId > 0,
            422,
            'ID pengguna login tidak valid.'
        );

        /*
        |----------------------------------------------------------------------
        | 1. ID langsung sudah ada di users.
        |----------------------------------------------------------------------
        */
        $existingById =
            DB::table('users')
                ->where('id', $authId)
                ->first();

        if ($existingById) {
            return (int) $existingById->id;
        }

        $attrs = method_exists($user, 'getAttributes')
            ? $user->getAttributes()
            : [];

        $email = trim(
            (string) (
                $attrs['email']
                ?? $user->email
                ?? ''
            )
        );

        $phone = trim(
            (string) (
                $attrs['no_hp']
                ?? $user->no_hp
                ?? ''
            )
        );

        $name = trim(
            (string) (
                $attrs['name']
                ?? $attrs['nama']
                ?? $attrs['nama_lengkap']
                ?? ''
            )
        );

        if ($name === '') {
            $name = $email !== ''
                ? trim((string) strstr($email, '@', true))
                : 'Pengguna #' . $authId;
        }

        if ($name === '') {
            $name = 'Pengguna #' . $authId;
        }

        /*
        |----------------------------------------------------------------------
        | 2. Jika email sudah ada di users, gunakan row tersebut.
        |----------------------------------------------------------------------
        */
        if (
            $email !== ''
            && Schema::hasColumn('users', 'email')
        ) {
            $existingByEmail =
                DB::table('users')
                    ->whereRaw(
                        'LOWER(TRIM(`email`)) = ?',
                        [strtolower($email)]
                    )
                    ->first();

            if ($existingByEmail) {
                return (int) $existingByEmail->id;
            }
        }

        /*
        |----------------------------------------------------------------------
        | 3. Buat shadow user agar FK users.id valid.
        |----------------------------------------------------------------------
        */
        $now = now();

        $row = [
            'id' => $authId,
            'name' => $name,
            'email' => $email !== ''
                ? $email
                : 'auth-user-' . $authId . '@local.invalid',
            'password' => (
                !empty($attrs['password'])
                ? (string) $attrs['password']
                : password_hash(
                    bin2hex(random_bytes(32)),
                    PASSWORD_BCRYPT
                )
            ),
        ];

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $row['email_verified_at'] = $attrs['email_verified_at'] ?? null;
        }

        if (Schema::hasColumn('users', 'remember_token')) {
            $row['remember_token'] = null;
        }

        if (Schema::hasColumn('users', 'created_at')) {
            $row['created_at'] = $now;
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            $row['updated_at'] = $now;
        }

        try {
            DB::table('users')->insert($row);
        } catch (Throwable $e) {
            /*
            |--------------------------------------------------------------
            | Race condition: request lain mungkin membuat row lebih dulu.
            |--------------------------------------------------------------
            */
            $existingAfterInsert =
                DB::table('users')
                    ->where('id', $authId)
                    ->first();

            if ($existingAfterInsert) {
                return (int) $existingAfterInsert->id;
            }

            report($e);

            throw new \RuntimeException(
                'User login gagal dipetakan ke users.id. '
                . 'Auth ID: ' . $authId
                . '. Email: ' . ($email ?: '(kosong)')
                . '. Detail: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return $authId;
    }


    /*
    |--------------------------------------------------------------------------
    | STORE TYPED COMMENT
    |--------------------------------------------------------------------------
    */

    private function storeTypedComment(
        Request $request,
        string $schoolId,
        string $documentId,
        string $documentType
    ): JsonResponse {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $commentUserId =
            $this->resolveCommentUserId($user);

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                $documentType
            );

        $validated =
            $request->validate(
                [
                    'page_number' =>
                        'nullable|integer|min:1',

                    'selected_text' =>
                        'nullable|string',

                    'comment_text' =>
                        'required|string',

                    'anchor_key' =>
                        'nullable|string|max:255',
                ]
            );

        try {
            $comment =
                AcademicDocumentWordComment::create(
                    [
                        'academic_document_id' =>
                            $document->id,

                        'user_id' =>
                            $commentUserId,

                        'page_number' =>
                            $validated['page_number']
                            ?? null,

                        'selected_text' =>
                            $validated['selected_text']
                            ?? null,

                        'comment_text' =>
                            $validated['comment_text'],

                        'anchor_key' =>
                            $validated['anchor_key']
                            ?? null,

                        'parent_id' =>
                            null,
                    ]
                );

            $comment->load(
                'user'
            );

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Komentar berhasil ditambahkan.',

                    'comment' =>
                        $comment,
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Gagal menambahkan komentar: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REPLY TYPED COMMENT
    |--------------------------------------------------------------------------
    */

    private function replyTypedComment(
        Request $request,
        string $schoolId,
        string $commentId,
        string $documentType
    ): JsonResponse {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $commentUserId =
            $this->resolveCommentUserId($user);

        $schoolId =
            (int) $schoolId;

        $commentId =
            (int) $commentId;

        $parent =
            AcademicDocumentWordComment::query()
                ->findOrFail(
                    $commentId
                );

        /*
        |----------------------------------------------------------------------
        | Ambil document langsung tanpa bergantung pada relation
        | academicDocument.
        |----------------------------------------------------------------------
        */

        $parentDocument =
            AcademicDocument::query()
                ->where(
                    'id',
                    $parent->academic_document_id
                )
                ->first();

        abort_unless(
            $parentDocument,
            404,
            'Dokumen komentar tidak ditemukan.'
        );

        $document =
            $this->getTypedDocumentOrFail(
                (int) $parentDocument->id,
                $schoolId,
                $documentType
            );

        $validated =
            $request->validate(
                [
                    'comment_text' =>
                        'required|string',
                ]
            );

        try {
            $reply =
                AcademicDocumentWordComment::create(
                    [
                        'academic_document_id' =>
                            $document->id,

                        'user_id' =>
                            $commentUserId,

                        'comment_text' =>
                            $validated['comment_text'],

                        'parent_id' =>
                            $parent->id,
                    ]
                );

            $reply->load(
                'user'
            );

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Balasan berhasil ditambahkan.',

                    'comment' =>
                        $reply,
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Gagal menambahkan balasan: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE TYPED COMMENT
    |--------------------------------------------------------------------------
    */

    private function resolveTypedComment(
        string $schoolId,
        string $commentId,
        string $documentType
    ): JsonResponse {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $commentId =
            (int) $commentId;

        $comment =
            AcademicDocumentWordComment::query()
                ->findOrFail(
                    $commentId
                );

        $documentId =
            (int) $comment->academic_document_id;

        /*
        |----------------------------------------------------------------------
        | Pastikan document benar-benar cocok.
        |----------------------------------------------------------------------
        */

        $this->getTypedDocumentOrFail(
            $documentId,
            $schoolId,
            $documentType
        );

        try {
            if ($comment->resolved_at) {
                $comment->update(
                    [
                        'resolved_at' =>
                            null,
                    ]
                );

                $message =
                    'Komentar dibuka kembali.';
            } else {
                $comment->update(
                    [
                        'resolved_at' =>
                            now(),
                    ]
                );

                $message =
                    'Komentar ditandai selesai.';
            }

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        $message,

                    'resolved' =>
                        !empty(
                            $comment->resolved_at
                        ),

                    'comment_id' =>
                        $comment->id,
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Gagal mengubah status komentar: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE TYPED COMMENT
    |--------------------------------------------------------------------------
    */

    private function destroyTypedComment(
        string $schoolId,
        string $commentId,
        string $documentType
    ): JsonResponse {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $commentId =
            (int) $commentId;

        $comment =
            AcademicDocumentWordComment::query()
                ->findOrFail(
                    $commentId
                );

        /*
        |----------------------------------------------------------------------
        | DOCUMENT
        |----------------------------------------------------------------------
        */

        $document =
            $this->getTypedDocumentOrFail(
                (int) $comment->academic_document_id,
                $schoolId,
                $documentType
            );

        /*
        |----------------------------------------------------------------------
        | Hanya komentar sendiri.
        |----------------------------------------------------------------------
        */

        $commentUserId =
            $this->resolveCommentUserId($user);

        if (
            (int) $comment->user_id
            !== (int) $commentUserId
        ) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Anda hanya dapat menghapus komentar sendiri.',
                ],
                403
            );
        }

        try {
            DB::transaction(
                function () use ($comment) {
                    /*
                    |----------------------------------------------------------
                    | DELETE REPLIES
                    |----------------------------------------------------------
                    */

                    AcademicDocumentWordComment::query()
                        ->where(
                            'parent_id',
                            $comment->id
                        )
                        ->delete();

                    /*
                    |----------------------------------------------------------
                    | DELETE COMMENT
                    |----------------------------------------------------------
                    */

                    $comment->delete();
                }
            );

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Komentar berhasil dihapus.',
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Gagal menghapus komentar: '
                        . $e->getMessage(),
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RPPM - FILE
    |--------------------------------------------------------------------------
    */

    public function file(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        int $documentId
    ) {
        return $this->fileTypedDocument(
            $schoolId,
            $documentId,
            self::RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFLEKSI - FILE
    |--------------------------------------------------------------------------
    */

    public function fileRefleksi(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        int $documentId
    ) {
        return $this->fileTypedDocument(
            $schoolId,
            $documentId,
            self::REFLEKSI_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILE TYPED DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function fileTypedDocument(
        string $schoolId,
        int $documentId,
        string $documentType
    ) {
        $this->abortIfCannotView();

        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                $documentType
            );

        abort_if(
            empty(
                $document->file_path
            ),
            404,
            'File Word belum tersedia.'
        );

        $disk =
            Storage::disk('public');

        abort_unless(
            $disk->exists(
                $document->file_path
            ),
            404,
            'File Word tidak ditemukan di storage.'
        );

        $filename =
            $document->original_filename
            ?: (
                $document->title
                ?: 'dokumen.docx'
            );

        return $disk->response(
            $document->file_path,
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                'Content-Disposition' =>
                    'inline; filename="'
                    . addslashes($filename)
                    . '"',

                'Cache-Control' =>
                    'no-store, no-cache, must-revalidate, max-age=0',

                'Pragma' =>
                    'no-cache',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - INDEX / TAMPILAN
    |--------------------------------------------------------------------------
    |
    | Menggunakan editor tamp-RPPM.
    | Tidak lagi mencari tamp-analisis-rppm.
    |--------------------------------------------------------------------------
    */

    public function analisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ) {
        $this->abortIfCannotView();

        $user =
            Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        /*
        |----------------------------------------------------------------------
        | AMBIL ANALISIS RPPM TERAKHIR
        |----------------------------------------------------------------------
        */

        $document =
            $this->getLatestTypedDocument(
                (int) $profile->school_partner_id,
                self::ANALISIS_RPPM_TYPE,
                $user
            );

        $wordContent = null;

        if ($document) {
            $wordContent =
                AcademicDocumentWordContent::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->latest('id')
                    ->first();
        }

        $comments = collect();

        if ($document) {
            $comments =
                AcademicDocumentWordComment::query()
                    ->where(
                        'academic_document_id',
                        $document->id
                    )
                    ->whereNull('parent_id')
                    ->with(
                        [
                            'user',
                            'replies.user',
                        ]
                    )
                    ->latest('id')
                    ->get();
        }

        $fileUrl = null;

        if (
            $document
            && !empty($document->file_path)
            && Storage::disk('public')
                ->exists(
                    $document->file_path
                )
        ) {
            $fileUrl =
                Storage::disk('public')
                    ->url(
                        $document->file_path
                    );
        }

        return view(
            self::RPPM_VIEW,
            [
                'document' =>
                    $document,

                'documentId' =>
                    $document?->id,

                'wordContent' =>
                    $wordContent,

                'comments' =>
                    $comments,

                'fileUrl' =>
                    $fileUrl,

                'isGuru' =>
                    $this->isGuru(),

                'currentRole' =>
                    $this->currentRole(),

                'role' =>
                    $role,

                'schoolName' =>
                    $schoolName,

                'schoolId' =>
                    $schoolId,

                'user' =>
                    $user,

                /*
                |--------------------------------------------------------------
                | Penanda agar Blade tahu ini Analisis RPPM.
                |--------------------------------------------------------------
                */

                'isAnalisisRppm' =>
                    true,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - UPLOAD
    |--------------------------------------------------------------------------
    */

    public function uploadAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId
    ): JsonResponse {
        if (!$this->isGuru()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Hanya Guru yang dapat mengunggah dokumen Analisis RPPM.',
                ],
                403
            );
        }

        $user =
            Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        abort_unless(
            $schoolId > 0,
            422,
            'ID sekolah tidak valid.'
        );

        $profile =
            $this->getSchoolProfileOrFail(
                $user,
                $schoolId
            );

        $teacherMapel =
            $this->getTeacherMapelOrFail(
                $user,
                $schoolId
            );

        $subjectId =
            (int) $teacherMapel->mapel_id;

        $response =
            $this->storeWordUpload(
                $request,
                $user,
                $profile,
                $subjectId,
                self::ANALISIS_RPPM_TYPE,
                self::ANALISIS_RPPM_DIRECTORY,
                'Dokumen Analisis RPPM berhasil diunggah.'
            );

        if (
            $response instanceof JsonResponse
            && $response->getStatusCode() === 200
        ) {
            $data =
                $response->getData(true);

            if (
                !empty($data['success'])
                && !empty($data['document_id'])
            ) {
                $data['edit_url'] =
                    route(
                        'lms.schoolAdmin.registrasiGuru.analisis-rppm.word.edit',
                        [
                            'role' =>
                                $role,

                            'schoolName' =>
                                $schoolName,

                            'schoolId' =>
                                $schoolId,

                            'documentId' =>
                                $data['document_id'],
                        ]
                    );

                return response()->json(
                    $data,
                    200
                );
            }
        }

        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - EDIT
    |--------------------------------------------------------------------------
    */

    public function editAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        $this->abortIfCannotView();

        $user =
            Auth::user();

        abort_unless(
            $user,
            401,
            'Pengguna belum login.'
        );

        $schoolId =
            (int) $schoolId;

        $documentId =
            (int) $documentId;

        $document =
            $this->getTypedDocumentOrFail(
                $documentId,
                $schoolId,
                self::ANALISIS_RPPM_TYPE
            );

        return $this->renderWordEditor(
            $document,
            $user,
            $role,
            $schoolName,
            $schoolId,
            self::RPPM_VIEW
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - SAVE
    |--------------------------------------------------------------------------
    */

    public function saveAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->saveTypedDocument(
            $request,
            $schoolId,
            $documentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - DOWNLOAD
    |--------------------------------------------------------------------------
    */

    public function downloadAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ) {
        return $this->downloadTypedDocument(
            $schoolId,
            $documentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - DELETE
    |--------------------------------------------------------------------------
    */

    public function destroyAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->destroyTypedDocument(
            $schoolId,
            $documentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - FILE
    |--------------------------------------------------------------------------
    */

    public function fileAnalisisRppm(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        int $documentId
    ) {
        return $this->fileTypedDocument(
            $schoolId,
            $documentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANALISIS RPPM - COMMENT
    |--------------------------------------------------------------------------
    */

    public function storeAnalisisRppmComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $documentId
    ): JsonResponse {
        return $this->storeTypedComment(
            $request,
            $schoolId,
            $documentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    public function replyAnalisisRppmComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->replyTypedComment(
            $request,
            $schoolId,
            $commentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    public function resolveAnalisisRppmComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->resolveTypedComment(
            $schoolId,
            $commentId,
            self::ANALISIS_RPPM_TYPE
        );
    }


    public function destroyAnalisisRppmComment(
        Request $request,
        string $role,
        string $schoolName,
        string $schoolId,
        string $commentId
    ): JsonResponse {
        return $this->destroyTypedComment(
            $schoolId,
            $commentId,
            self::ANALISIS_RPPM_TYPE
        );
    }

public function browseAcademicDocuments(
    Request $request,
    string $role,
    string $schoolName,
    int $schoolId,
    string $type
) {
    $query = AcademicDocument::query()
        ->where('school_partner_id', $schoolId)
        ->where('document_type', self::RPPM_TYPE);

    if ($request->filled('search')) {
        $search = $request->string('search')->toString();

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('original_filename', 'like', "%{$search}%");
        });
    }

    $documents = $query
        ->latest('id')
        ->limit(20)
        ->get()
        ->map(function ($document) use ($role, $schoolName, $schoolId) {
    return [
        'id' => $document->id,
        'title' => $document->title ?: $document->original_filename,
        'original_filename' => $document->original_filename,

        'is_saved' => (
            Schema::hasColumn('academic_documents', 'status')
                ? $document->status === 'saved'
                : false
        ),

        'view_url' => route(
            'lms.schoolAdmin.registrasiGuru.academicWord.sharedView',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'type' => 'rppm',
                'documentId' => $document->id,
            ]
        ),
    ];
});

    return response()->json([
        'data' => $documents,
    ]);
}

public function saveArchive(
    Request $request,
    string $role,
    string $schoolName,
    string $schoolId,
    string $type,
    string $document
): JsonResponse {
    if (!$this->isGuru()) {
        return response()->json([
            'success' => false,
            'message' => 'Hanya Guru yang dapat menyimpan dokumen.',
        ], 403);
    }

    $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'content' => ['nullable', 'string'],
    ]);

    $documentModel = $this->getTypedDocumentOrFail(
        (int) $document,
        (int) $schoolId,
        self::RPPM_TYPE
    );

    $content = AcademicDocumentWordContent::query()
        ->where('academic_document_id', $documentModel->id)
        ->latest('id')
        ->first();

    $title = trim($request->input('title'));

    if ($content) {
        $content->update([
            'title' => $title,
            'content' => $request->input('content', $content->content),
            'last_saved_at' => now(),
        ]);
    } else {
        AcademicDocumentWordContent::create([
            'academic_document_id' => $documentModel->id,
            'title' => $title,
            'content' => $request->input('content'),
            'last_saved_at' => now(),
        ]);
    }

    $documentModel->update([
        'title' => $title,
    ]);

    if (Schema::hasColumn('academic_documents', 'status')) {
        $documentModel->update([
            'status' => 'saved',
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'RPPM berhasil disimpan ke Drive.',
    ]);
}
}
