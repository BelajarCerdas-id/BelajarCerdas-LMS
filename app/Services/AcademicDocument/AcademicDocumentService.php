<?php

namespace App\Services\AcademicDocument;

use App\Models\AcademicDocument;
use App\Models\TeacherMapel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AcademicDocumentService
{
    private static ?array $documentColumnsCache = null;

    public function documentColumnExists(string $column): bool
    {
        if (self::$documentColumnsCache === null) {
            try {
                self::$documentColumnsCache = Schema::getColumnListing('academic_documents');
            } catch (Throwable) {
                return true;
            }
        }

        return in_array($column, self::$documentColumnsCache, true);
    }

    public function documentOwnerColumn(): ?string
    {
        foreach (['owner_user_id', 'user_id', 'created_by', 'uploaded_by'] as $column) {
            if ($this->documentColumnExists($column)) {
                return $column;
            }
        }

        return null;
    }

    public function applyDocumentOwner($query, int $userId)
    {
        $ownerColumn = $this->documentOwnerColumn();
        if ($ownerColumn) {
            $query->where($ownerColumn, $userId);
        }

        return $query;
    }

    public function getDocumentForSchoolOrFail(
        int $schoolId,
        int $documentId,
        ?string $documentType = null,
        bool $ownerOnly = false,
        ?int $ownerUserId = null
    ): AcademicDocument {
        $query = AcademicDocument::query()->where('id', $documentId);

        if ($this->documentColumnExists('school_partner_id')) {
            $query->where('school_partner_id', $schoolId);
        }

        if ($documentType && $this->documentColumnExists('document_type')) {
            $query->where('document_type', $documentType);
        }

        if ($ownerOnly) {
            $this->applyDocumentOwner($query, $ownerUserId ?? (int) Auth::id());
        }

        $document = $query->first();
        abort_unless($document, 404, 'Dokumen akademik tidak ditemukan.');

        return $document;
    }

    public function getSchoolUserIds(int $schoolId): Collection
    {
        $ownerColumn = $this->documentOwnerColumn();
        if (! $ownerColumn || ! $this->documentColumnExists('school_partner_id')) {
            return collect();
        }

        return AcademicDocument::query()
            ->where('school_partner_id', $schoolId)
            ->whereNotNull($ownerColumn)
            ->pluck($ownerColumn)
            ->filter()
            ->unique()
            ->values();
    }

    public function getTeacherMapel(int $schoolId, bool $isGuru, ?int $teacherId = null): Collection
    {
        $query = TeacherMapel::query()
            ->with(['Mapel', 'SchoolClass'])
            ->where('is_active', true);

        if ($isGuru && $teacherId) {
            $query->where('user_id', $teacherId);
        }

        try {
            $query->whereHas('SchoolClass', function ($q) use ($schoolId) {
                if (Schema::hasColumn('school_classes', 'school_partner_id')) {
                    $q->where('school_partner_id', $schoolId);
                } elseif (Schema::hasColumn('school_classes', 'school_id')) {
                    $q->where('school_id', $schoolId);
                }
            });
        } catch (Throwable) {
        }

        return $query->get();
    }

    public function resolveCommentAuthor(?int $userId = null): array
    {
        $userId = (int) ($userId ?? Auth::id());
        $name = null;
        $role = null;

        if ($userId > 0 && Schema::hasTable('user_accounts') && Schema::hasColumn('user_accounts', 'id')) {
            $account = DB::table('user_accounts')->where('id', $userId)->first();
            if ($account) {
                foreach (['name', 'nama', 'nama_lengkap', 'full_name', 'display_name', 'username', 'email'] as $field) {
                    $value = $account->{$field} ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        $name = trim($value);
                        break;
                    }
                }
                foreach (['role', 'jabatan', 'position'] as $field) {
                    $value = $account->{$field} ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        $role = trim($value);
                        break;
                    }
                }
            }
        }

        $authUser = Auth::user();
        $name = $name ?? $authUser?->name ?? $authUser?->nama ?? $authUser?->nama_lengkap ?? $authUser?->full_name ?? $authUser?->username ?? $authUser?->email ?? 'Pengguna';
        $role = $role ?? $authUser?->role ?? $authUser?->jabatan ?? '';

        return [
            'name' => (string) $name,
            'role' => (string) $role,
        ];
    }
}
