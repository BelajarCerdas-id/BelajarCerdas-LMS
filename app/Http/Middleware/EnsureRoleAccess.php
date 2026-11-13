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
