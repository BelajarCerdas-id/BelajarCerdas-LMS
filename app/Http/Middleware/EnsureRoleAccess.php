<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compares the logged-in user's role with the role the URL claims. The URL
 * carries the role as a segment ({role} route parameter, or the
 * administrator/library/finance prefixes). A segment that is not a known role
 * slug (feature prefixes like syllabus, cache, school-subscription) says
 * nothing about a role and is allowed — that keeps unmapped features working
 * while every role-named URL is now checked.
 */
class EnsureRoleAccess
{
    /** shortcut: only role-named URLs are enforced; unmapped slugs fall through, tighten the map when a new role area is added */
    private const ADMIN_ROLES = ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum'];

    /** Admin family plus finance, who own the contract console (sidebar shows it under Finance). */
    private const CONTRACT_ROLES = ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum', 'finance'];

    /**
     * Feature prefixes (after lms/{role}, or the whole non-lms path) whose
     * controllers have no internal role check and are role-gated only here,
     * whatever role slug the URL carries. Verified against routes/web.php.
     */
    private const FEATURE_ROLES = [
        'office-management' => self::ADMIN_ROLES, // OfficeManagementController (incl. manage-user)
        'manage-user' => self::ADMIN_ROLES,
        'school-foundation' => self::ADMIN_ROLES, // FoundationManagementController
        'tka-tryout-management' => self::ADMIN_ROLES, // TkaTryout* management controllers
        'manage-contract' => self::CONTRACT_ROLES, // FinanceContractController / FinanceDashboardController
        'school-subscription/validate' => self::ADMIN_ROLES, // SchoolPartnerController bulk upload
        'school-subscription/store' => self::ADMIN_ROLES,
        'school-subscription/add-users' => self::ADMIN_ROLES,
    ];

    /**
     * Account/class/curriculum management under school-subscription: the views
     * themselves check for Administrator/Admin Sekolah, and the write routes
     * (activate-account, activate-major/class/student, mapel/bab store) have no
     * internal role check. Matched anywhere inside the school-subscription area
     * because {schoolId} sits between the prefix and the management segment.
     */
    private const SCHOOL_SUBSCRIPTION_MANAGEMENT_SEGMENTS = [
        'management-account', 'management-role-account', 'management-class',
        'management-majors', 'management-students', 'academic-management',
    ];

    /** URL role slug (lowercase) => roles allowed there. */
    private const ROLE_MAP = [
        'student' => ['siswa'],
        'siswa' => ['siswa'],
        'teacher' => ['guru'],
        'guru' => ['guru'],
        'parent' => ['orang tua'],
        'orang tua' => ['orang tua'],
        'ortu' => ['orang tua'],
        'headmaster' => ['kepala sekolah'],
        'kepsek' => ['kepala sekolah'],
        'kepala sekolah' => ['kepala sekolah'],
        'wakil kepala sekolah' => ['wakil kepala sekolah'],
        'student-vice-principal' => ['wakil kesiswaan'],
        'wakil kesiswaan' => ['wakil kesiswaan'],
        'administrator' => self::ADMIN_ROLES,
        'admin' => self::ADMIN_ROLES,
        'admin sekolah' => ['admin sekolah'],
        'school-admin' => ['admin sekolah'],
        'foundation' => ['yayasan'],
        'yayasan' => ['yayasan'],
        'finance' => ['finance', 'administrator'],
        // Non-lms prefixes that are admin-only areas
        'library' => self::ADMIN_ROLES,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $allowed = $this->allowedRoles($request->path());

        if ($allowed !== null && !in_array($this->normalize($request->user()?->role), $allowed, true)) {
            abort(403, 'Akses ditolak: halaman ini bukan untuk peran Anda.');
        }

        return $next($request);
    }

    /** Roles allowed at this URL, or null when the URL says nothing about a role. */
    private function allowedRoles(string $path): ?array
    {
        $segments = explode('/', trim($path, '/'));
        // Feature part: after lms/{role} for lms paths, the whole path otherwise.
        $feature = str_starts_with($path, 'lms/')
            ? implode('/', array_slice($segments, 2))
            : implode('/', $segments);

        // Feature-gated areas are checked before the role slug: the slug says nothing there.
        $featureNorm = $this->normalize($feature);
        foreach (self::FEATURE_ROLES as $adminFeature => $roles) {
            if ($featureNorm === $adminFeature || str_starts_with($featureNorm, $adminFeature . '/')) {
                return $roles;
            }
        }
        if ($featureNorm === 'school-subscription' || str_starts_with($featureNorm, 'school-subscription/')) {
            foreach (self::SCHOOL_SUBSCRIPTION_MANAGEMENT_SEGMENTS as $managementSegment) {
                if (str_starts_with($featureNorm, 'school-subscription/' . $managementSegment)
                    || str_contains($featureNorm, '/' . $managementSegment . '/')) {
                    return self::ADMIN_ROLES;
                }
            }
        }

        $segment = str_starts_with($path, 'lms/')
            ? ($segments[1] ?? '')
            : ($segments[0] ?? '');

        return self::ROLE_MAP[$this->normalize($segment)] ?? null;
    }

    private function normalize(?string $role): string
    {
        // Laravel's Request::path() is already URL-decoded, so "Kepala Sekolah" arrives with a space.
        return strtolower(trim((string) $role));
    }
}
