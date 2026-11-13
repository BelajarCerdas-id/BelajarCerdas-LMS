<?php

namespace App\Http\Middleware;

use App\Models\SchoolFoundationProfile;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use App\Models\TeacherMapel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route {schoolId} (dan school_partner_id) adalah id sekolah yang dibawa di URL.
 * Role non-admin tidak boleh mengakses data sekolah lain hanya dengan mengubah
 * angka di URL, jadi keanggotaan diverifikasi di sini.
 */
class EnsureSchoolMemberAccess
{
    // shortcut: di-copy dari EnsureRoleAccess; kalau daftar role admin berubah harus ikut diubah
    private const ADMIN_ROLES = ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || in_array($user->role, self::ADMIN_ROLES, true)) {
            return $next($request);
        }

        $schoolId = $request->route('schoolId') ?? $request->route('school_partner_id');

        if ($schoolId === null) {
            return $next($request);
        }

        // sekolah tidak ada di DB -> biarkan controller/route berikutnya yang 404
        if (!SchoolPartner::where('id', $schoolId)->exists()) {
            return $next($request);
        }

        $isMember =
            // guru: mapel yang diajarnya berada di kelas milik sekolah tersebut
            TeacherMapel::where('user_id', $user->id)
                ->whereHas('SchoolClass', fn($q) => $q->where('school_partner_id', $schoolId))
                ->exists()
            || StudentProfile::where('user_id', $user->id)->where('school_partner_id', $schoolId)->exists()
            || SchoolStaffProfile::where('user_id', $user->id)->where('school_partner_id', $schoolId)->exists()
            // admin yayasan: profil yayasan yang memiliki sekolah tersebut
            || SchoolFoundationProfile::where('user_id', $user->id)
                ->whereHas('SchoolFoundation.SchoolPartner', fn($q) => $q->where('school_partners.id', $schoolId))
                ->exists();

        if ($isMember) {
            return $next($request);
        }

        // fail-open: user tanpa profil keanggotaan apa pun (data belum lengkap, role lama,
        // relasi model ambigu seperti orang tua) tidak bisa dipastikan statusnya, jadi diteruskan
        $knownAny = StudentProfile::where('user_id', $user->id)->exists()
            || SchoolStaffProfile::where('user_id', $user->id)->exists()
            || TeacherMapel::where('user_id', $user->id)->exists()
            || SchoolFoundationProfile::where('user_id', $user->id)->exists();

        if (!$knownAny) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke sekolah ini.');
    }
}
